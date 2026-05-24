const { createClient } = require('@supabase/supabase-js');
const jwt = require('jsonwebtoken');

const supabase = () => createClient(process.env.SUPABASE_URL, process.env.SUPABASE_SERVICE_KEY);
const JWT_SECRET = process.env.JWT_SECRET || 'kozhen-studio-secret-2025';
const ADMIN_USER = process.env.ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.ADMIN_PASS || 'Admin2025';
const headers = { 'Access-Control-Allow-Origin': '*', 'Access-Control-Allow-Headers': 'Content-Type, Authorization', 'Content-Type': 'application/json' };

function verifyAdmin(event) {
  const auth = event.headers.authorization || event.headers.Authorization || '';
  const token = auth.replace('Bearer ', '');
  if (!token) return null;
  try {
    const payload = jwt.verify(token, JWT_SECRET);
    return payload.admin ? payload : null;
  } catch { return null; }
}

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') return { statusCode: 200, headers };

  if (event.httpMethod === 'POST') {
    let body;
    try { body = JSON.parse(event.body || '{}'); } catch { return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid JSON' }) }; }

    if (body.action === 'login') {
      if (body.username !== ADMIN_USER || body.password !== ADMIN_PASS) {
        return { statusCode: 401, headers, body: JSON.stringify({ error: 'بيانات المدير غير صحيحة' }) };
      }
      const token = jwt.sign({ admin: true }, JWT_SECRET, { expiresIn: '12h' });
      return { statusCode: 200, headers, body: JSON.stringify({ token }) };
    }

    const admin = verifyAdmin(event);
    if (!admin) return { statusCode: 401, headers, body: JSON.stringify({ error: 'غير مصرح' }) };

    const db = supabase();

    if (body.action === 'approve_subscription') {
      const { user_id, plan, months } = body;
      const sub_start = new Date().toISOString().split('T')[0];
      const sub_end = new Date(Date.now() + (months || 1) * 30 * 86400000).toISOString().split('T')[0];
      const { error } = await db.from('users').update({ subscription_status: plan, subscription_end: sub_end }).eq('id', user_id);
      if (!error) await db.from('subscription_requests').update({ status: 'approved' }).eq('user_id', user_id).eq('status', 'pending');
      return error ? { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في التحديث' }) } : { statusCode: 200, headers, body: JSON.stringify({ success: true }) };
    }

    if (body.action === 'reject_subscription') {
      await db.from('subscription_requests').update({ status: 'rejected' }).eq('user_id', body.user_id).eq('status', 'pending');
      return { statusCode: 200, headers, body: JSON.stringify({ success: true }) };
    }

    if (body.action === 'delete_user') {
      await db.from('users').delete().eq('id', body.user_id);
      return { statusCode: 200, headers, body: JSON.stringify({ success: true }) };
    }

    return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid action' }) };
  }

  if (event.httpMethod === 'GET') {
    const admin = verifyAdmin(event);
    if (!admin) return { statusCode: 401, headers, body: JSON.stringify({ error: 'غير مصرح' }) };

    const db = supabase();
    const action = event.queryStringParameters?.action;

    if (action === 'users' || !action) {
      const { data: users } = await db.from('users').select('id,username,email,restaurant_name,subscription_status,trial_end,subscription_end,created_at').order('created_at', { ascending: false });
      const { data: requests } = await db.from('subscription_requests').select('*').eq('status', 'pending').order('created_at', { ascending: false });
      const { count: totalProducts } = await db.from('products').select('id', { count: 'exact', head: true });
      return { statusCode: 200, headers, body: JSON.stringify({ users: users || [], pending_requests: requests || [], total_products: totalProducts || 0 }) };
    }
  }

  return { statusCode: 405, headers, body: JSON.stringify({ error: 'Method Not Allowed' }) };
};
