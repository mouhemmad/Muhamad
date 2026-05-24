const { createClient } = require('@supabase/supabase-js');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

const supabase = () => createClient(process.env.SUPABASE_URL, process.env.SUPABASE_SERVICE_KEY);
const JWT_SECRET = process.env.JWT_SECRET || 'kozhen-studio-secret-2025';
const TRIAL_DAYS = 30;

const headers = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'Content-Type, Authorization',
  'Content-Type': 'application/json',
};

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') return { statusCode: 200, headers };
  if (event.httpMethod !== 'POST') return { statusCode: 405, headers, body: JSON.stringify({ error: 'Method Not Allowed' }) };

  const db = supabase();
  let body;
  try { body = JSON.parse(event.body || '{}'); } catch { return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid JSON' }) }; }

  const { action } = body;

  if (action === 'login') {
    const { username, password } = body;
    if (!username || !password) return { statusCode: 400, headers, body: JSON.stringify({ error: 'البيانات ناقصة' }) };

    const { data: users } = await db.from('users').select('*').or(`username.eq.${username},email.eq.${username}`).limit(1);
    const user = users?.[0];

    if (!user || !(await bcrypt.compare(password, user.password))) {
      return { statusCode: 401, headers, body: JSON.stringify({ error: 'اسم المستخدم أو كلمة المرور غير صحيحة' }) };
    }

    const token = jwt.sign({ id: user.id, username: user.username }, JWT_SECRET, { expiresIn: '7d' });
    const { password: _, ...safeUser } = user;
    return { statusCode: 200, headers, body: JSON.stringify({ token, user: safeUser }) };
  }

  if (action === 'register') {
    const { username, email, password, restaurant_name } = body;
    if (!username || !email || !password || !restaurant_name) return { statusCode: 400, headers, body: JSON.stringify({ error: 'البيانات ناقصة' }) };
    if (!/^[a-zA-Z0-9_]+$/.test(username)) return { statusCode: 400, headers, body: JSON.stringify({ error: 'اسم المستخدم يجب أن يحتوي على أحرف إنجليزية وأرقام فقط' }) };
    if (password.length < 6) return { statusCode: 400, headers, body: JSON.stringify({ error: 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' }) };

    const password_hash = await bcrypt.hash(password, 10);
    const today = new Date();
    const trial_start = today.toISOString().split('T')[0];
    const trial_end = new Date(today.getTime() + TRIAL_DAYS * 86400000).toISOString().split('T')[0];

    const { data, error } = await db.from('users').insert({
      username, email, password: password_hash, restaurant_name,
      subscription_status: 'trial', selected_theme: 'dark',
      trial_start, trial_end,
      social_tiktok: '', social_instagram: '', social_snapchat: '',
      social_location: '', social_whatsapp: '', social_facebook: '',
      video_url: '', hero_image: '',
    }).select().single();

    if (error) {
      const msg = error.message?.includes('unique') ? 'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقاً' : 'خطأ في إنشاء الحساب';
      return { statusCode: 400, headers, body: JSON.stringify({ error: msg }) };
    }

    await db.from('categories').insert([
      { user_id: data.id, slug: 'main', name: 'الأطباق الرئيسية', icon: 'utensils', color: '#8b5cf6', sort_order: 1 },
      { user_id: data.id, slug: 'appetizer', name: 'المقبلات', icon: 'leaf', color: '#22c55e', sort_order: 2 },
      { user_id: data.id, slug: 'drinks', name: 'المشروبات', icon: 'coffee', color: '#3b82f6', sort_order: 3 },
      { user_id: data.id, slug: 'desserts', name: 'الحلويات', icon: 'cookie', color: '#ec4899', sort_order: 4 },
    ]);

    const token = jwt.sign({ id: data.id, username: data.username }, JWT_SECRET, { expiresIn: '7d' });
    const { password: _, ...safeUser } = data;
    return { statusCode: 200, headers, body: JSON.stringify({ token, user: safeUser }) };
  }

  return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid action' }) };
};
