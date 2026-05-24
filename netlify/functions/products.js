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
    const { data, error } = await db.from('products').select('*').eq('user_id', user.id).eq('is_available', true).order('created_at', { ascending: false });
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في جلب المنتجات' }) };
    return { statusCode: 200, headers, body: JSON.stringify({ products: data }) };
  }

  if (event.httpMethod === 'POST') {
    let body;
    try { body = JSON.parse(event.body || '{}'); } catch { return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid JSON' }) }; }
    const { name, description, price, category, image_url } = body;
    if (!name || price === undefined) return { statusCode: 400, headers, body: JSON.stringify({ error: 'الاسم والسعر مطلوبان' }) };

    const { data: userData } = await db.from('users').select('subscription_status').eq('id', user.id).single();
    const { count } = await db.from('products').select('id', { count: 'exact', head: true }).eq('user_id', user.id).eq('is_available', true);
    const limits = { trial: 10, basic: 50, pro: 999999 };
    const limit = limits[userData?.subscription_status] ?? 10;
    if (count >= limit) return { statusCode: 403, headers, body: JSON.stringify({ error: `وصلت للحد الأقصى (${limit} منتج) في باقتك` }) };

    const { data, error } = await db.from('products').insert({ user_id: user.id, name, description: description || '', price: parseFloat(price), category: category || 'main', image_url: image_url || '' }).select().single();
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في إضافة المنتج' }) };
    return { statusCode: 200, headers, body: JSON.stringify({ product: data }) };
  }

  if (event.httpMethod === 'PUT') {
    let body;
    try { body = JSON.parse(event.body || '{}'); } catch { return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid JSON' }) }; }
    const { id, name, description, price, category, image_url } = body;
    if (!id) return { statusCode: 400, headers, body: JSON.stringify({ error: 'id مطلوب' }) };

    const update = {};
    if (name !== undefined) update.name = name;
    if (description !== undefined) update.description = description;
    if (price !== undefined) update.price = parseFloat(price);
    if (category !== undefined) update.category = category;
    if (image_url !== undefined) update.image_url = image_url;

    const { error } = await db.from('products').update(update).eq('id', id).eq('user_id', user.id);
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في التحديث' }) };
    return { statusCode: 200, headers, body: JSON.stringify({ success: true }) };
  }

  if (event.httpMethod === 'DELETE') {
    const id = event.queryStringParameters?.id;
    if (!id) return { statusCode: 400, headers, body: JSON.stringify({ error: 'id مطلوب' }) };
    const { error } = await db.from('products').delete().eq('id', id).eq('user_id', user.id);
    if (error) return { statusCode: 500, headers, body: JSON.stringify({ error: 'خطأ في الحذف' }) };
    return { statusCode: 200, headers, body: JSON.stringify({ success: true }) };
  }

  return { statusCode: 405, headers, body: JSON.stringify({ error: 'Method Not Allowed' }) };
};
