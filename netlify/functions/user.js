const { createClient } = require('@supabase/supabase-js');
const jwt = require('jsonwebtoken');

const supabase = () => createClient(process.env.SUPABASE_URL, process.env.SUPABASE_SERVICE_KEY);
const JWT_SECRET = process.env.JWT_SECRET || 'kozhen-studio-secret-2025';

const headers = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'Content-Type, Authorization',
  'Content-Type': 'application/json',
};

function verifyToken(event) {
  const auth = event.headers.authorization || event.headers.Authorization || '';
  const token = auth.replace('Bearer ', '');
  if (!token) return null;
  try { return jwt.verify(token, JWT_SECRET); } catch { return null; }
}

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') return { statusCode: 200, headers };

  const user = verifyToken(event);
  if (!user) return { statusCode: 401, headers, body: JSON.stringify({ error: 'غير مصرح' }) };

  const db = supabase();

  if (event.httpMethod === 'GET') {
    const { data, error } = await db.from('users').select('id,username,email,restaurant_name,subscription_status,selected_theme,trial_start,trial_end,subscription_end,social_tiktok,social_instagram,social_snapchat,social_location,social_whatsapp,social_facebook,video_url,hero_image,created_at').eq('id', user.id).single();
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في جلب البيانات' }) };

    const today = new Date().toISOString().split('T')[0];
    let sub = { status: 'inactive' };
    if (data.subscription_status === 'trial') {
      if (today > data.trial_end) sub = { status: 'expired', message: 'انتهت الفترة التجريبية' };
      else { const days = Math.ceil((new Date(data.trial_end) - new Date(today)) / 86400000); sub = { status: 'active', plan: 'trial', days_left: days }; }
    } else if (data.subscription_status === 'basic' || data.subscription_status === 'pro') {
      if (today > data.subscription_end) sub = { status: 'expired', message: 'انتهى الاشتراك' };
      else { const days = Math.ceil((new Date(data.subscription_end) - new Date(today)) / 86400000); sub = { status: 'active', plan: data.subscription_status, days_left: days }; }
    }

    return { statusCode: 200, headers, body: JSON.stringify({ user: data, subscription: sub }) };
  }

  if (event.httpMethod === 'PUT') {
    let body;
    try { body = JSON.parse(event.body || '{}'); } catch { return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid JSON' }) }; }

    const allowed = ['restaurant_name', 'selected_theme', 'social_tiktok', 'social_instagram', 'social_snapchat', 'social_location', 'social_whatsapp', 'social_facebook', 'video_url', 'hero_image'];
    const update = {};
    allowed.forEach(k => { if (body[k] !== undefined) update[k] = body[k]; });

    const { error } = await db.from('users').update(update).eq('id', user.id);
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في التحديث' }) };
    return { statusCode: 200, headers, body: JSON.stringify({ success: true }) };
  }

  return { statusCode: 405, headers, body: JSON.stringify({ error: 'Method Not Allowed' }) };
};
