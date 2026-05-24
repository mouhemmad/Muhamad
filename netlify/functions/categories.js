const { createClient } = require('@supabase/supabase-js');
const jwt = require('jsonwebtoken');

const supabase = () => createClient(process.env.SUPABASE_URL, process.env.SUPABASE_SERVICE_KEY);
const JWT_SECRET = process.env.JWT_SECRET || 'kozhen-studio-secret-2025';
const headers = { 'Access-Control-Allow-Origin': '*', 'Access-Control-Allow-Headers': 'Content-Type, Authorization', 'Content-Type': 'application/json' };

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
    const { data, error } = await db.from('categories').select('*').eq('user_id', user.id).order('sort_order', { ascending: true });
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في جلب الفئات' }) };
    return { statusCode: 200, headers, body: JSON.stringify({ categories: data }) };
  }

  if (event.httpMethod === 'POST') {
    let body;
    try { body = JSON.parse(event.body || '{}'); } catch { return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid JSON' }) }; }
    const { slug, name, icon, color } = body;
    if (!name) return { statusCode: 400, headers, body: JSON.stringify({ error: 'الاسم مطلوب' }) };

    const cleanSlug = (slug || '').replace(/[^a-z0-9_]/g, '').toLowerCase() || 'cat_' + Date.now();
    const { data: maxData } = await db.from('categories').select('sort_order').eq('user_id', user.id).order('sort_order', { ascending: false }).limit(1);
    const maxOrder = maxData?.[0]?.sort_order ?? 0;

    const { data, error } = await db.from('categories').insert({ user_id: user.id, slug: cleanSlug, name, icon: icon || 'utensils', color: color || '#8b5cf6', sort_order: maxOrder + 1 }).select().single();
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في إضافة الفئة' }) };
    return { statusCode: 200, headers, body: JSON.stringify({ category: data }) };
  }

  if (event.httpMethod === 'DELETE') {
    const id = event.queryStringParameters?.id;
    if (!id) return { statusCode: 400, headers, body: JSON.stringify({ error: 'id مطلوب' }) };
    const { error } = await db.from('categories').delete().eq('id', id).eq('user_id', user.id);
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في الحذف' }) };
    return { statusCode: 200, headers, body: JSON.stringify({ success: true }) };
  }

  return { statusCode: 405, headers, body: JSON.stringify({ error: 'Method Not Allowed' }) };
};
