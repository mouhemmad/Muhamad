-- Run this in your Supabase SQL Editor

CREATE TABLE IF NOT EXISTS users (
  id BIGSERIAL PRIMARY KEY,
  username TEXT UNIQUE NOT NULL,
  email TEXT UNIQUE NOT NULL,
  password TEXT NOT NULL,
  restaurant_name TEXT NOT NULL,
  subscription_status TEXT DEFAULT 'trial',
  selected_theme TEXT DEFAULT 'dark',
  trial_start DATE,
  trial_end DATE,
  subscription_end DATE,
  social_tiktok TEXT DEFAULT '',
  social_instagram TEXT DEFAULT '',
  social_snapchat TEXT DEFAULT '',
  social_location TEXT DEFAULT '',
  social_whatsapp TEXT DEFAULT '',
  social_facebook TEXT DEFAULT '',
  video_url TEXT DEFAULT '',
  hero_image TEXT DEFAULT '',
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS products (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
  name TEXT NOT NULL,
  description TEXT DEFAULT '',
  price REAL NOT NULL,
  category TEXT DEFAULT 'main',
  image_url TEXT DEFAULT '',
  is_available BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS categories (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
  slug TEXT NOT NULL,
  name TEXT NOT NULL,
  icon TEXT DEFAULT 'utensils',
  color TEXT DEFAULT '#8b5cf6',
  sort_order INTEGER DEFAULT 0,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS subscription_requests (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
  plan TEXT NOT NULL,
  duration_months INTEGER DEFAULT 1,
  phone TEXT DEFAULT '',
  notes TEXT DEFAULT '',
  status TEXT DEFAULT 'pending',
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Disable Row Level Security (functions use service key)
ALTER TABLE users DISABLE ROW LEVEL SECURITY;
ALTER TABLE products DISABLE ROW LEVEL SECURITY;
ALTER TABLE categories DISABLE ROW LEVEL SECURITY;
ALTER TABLE subscription_requests DISABLE ROW LEVEL SECURITY;
