const { createClient } = require('@supabase/supabase-js');

const supabase = () => createClient(process.env.SUPABASE_URL, process.env.SUPABASE_SERVICE_KEY);
const headers = { 'Access-Control-Allow-Origin': '*', 'Access-Control-Allow-Headers': 'Content-Type', 'Content-Type': 'application/json' };

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') return { statusCode: 200, headers };
  if (event.httpMethod !== 'GET') return { statusCode: 405, headers, body: JSON.stringify({ error: 'Method Not Allowed' }) };

  const username = event.queryStringParameters?.u || event.queryStringParameters?.username;
  if (!username) return { statusCode: 400, headers, body: JSON.stringify({ error: 'اسم المستخدم مطلوب' }) };

  const db = supabase();
  const { data: user, error: userErr } = await db.from('users')
    .select('id,username,restaurant_name,subscription_status,selected_theme,trial_end,subscription_end,social_tiktok,social_instagram,social_snapchat,social_location,social_whatsapp,social_facebook,video_url,hero_image')
    .eq('username', username).single();

  if (userErr || !user) return { statusCode: 404, headers, body: JSON.stringify({ error: 'المطعم غير موجود' }) };

  const today = new Date().toISOString().split('T')[0];
  let active = false;
  if (user.subscription_status === 'trial' && today <= user.trial_end) active = true;
  if ((user.subscription_status === 'basic' || user.subscription_status === 'pro') && today <= user.subscription_end) active = true;

  if (!active) return { statusCode: 403, headers, body: JSON.stringify({ error: 'هذا المطعم غير متاح حالياً' }) };

  const [{ data: products }, { data: categories }] = await Promise.all([
    db.from('products').select('*').eq('user_id', user.id).eq('is_available', true).order('created_at', { ascending: false }),
    db.from('categories').select('*').eq('user_id', user.id).order('sort_order', { ascending: true }),
  ]);

  return { statusCode: 200, headers, body: JSON.stringify({ user, products: products || [], categories: categories || [] }) };
};
