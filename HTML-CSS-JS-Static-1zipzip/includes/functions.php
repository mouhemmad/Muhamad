<?php
function getThemes() {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT * FROM themes WHERE is_active = 1");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProducts($user_id) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM products WHERE user_id = ? AND is_available = 1 ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProduct($product_id, $user_id) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCategories($user_id) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addCategory($user_id, $slug, $name, $icon, $color) {
    $db = Database::getConnection();
    $slug = preg_replace('/[^a-z0-9_]/','', strtolower($slug)) ?: 'cat_'.time();
    $stmt = $db->prepare("SELECT MAX(sort_order) FROM categories WHERE user_id=?");
    $stmt->execute([$user_id]);
    $max = (int)$stmt->fetchColumn();
    $ins = $db->prepare("INSERT INTO categories (user_id,slug,name,icon,color,sort_order) VALUES (?,?,?,?,?,?)");
    return $ins->execute([$user_id, $slug, $name, $icon, $color, $max+1]);
}

function deleteCategory($cat_id, $user_id) {
    $db = Database::getConnection();
    $stmt = $db->prepare("DELETE FROM categories WHERE id=? AND user_id=?");
    return $stmt->execute([$cat_id, $user_id]);
}

function uploadProductImage($image) {
    if (!$image || $image['error'] !== 0) return '';
    $upload_dir = __DIR__ . '/../uploads/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return '';
    $filename = uniqid('p_') . '.' . $ext;
    if (move_uploaded_file($image['tmp_name'], $upload_dir . $filename)) {
        return 'uploads/' . $filename;
    }
    return '';
}

function addProduct($user_id, $name, $description, $price, $category, $image) {
    $db = Database::getConnection();
    $image_url = uploadProductImage($image);
    $stmt = $db->prepare("INSERT INTO products (user_id, name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $name, $description, $price, $category, $image_url]);
}

function updateProduct($product_id, $user_id, $data, $image = null) {
    $db = Database::getConnection();
    if ($image && $image['error'] === 0) {
        $image_url = uploadProductImage($image);
        $sql = "UPDATE products SET name=?, description=?, price=?, category=?, image_url=? WHERE id=? AND user_id=?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$data['name'], $data['description'], $data['price'], $data['category'], $image_url, $product_id, $user_id]);
    } else {
        $sql = "UPDATE products SET name=?, description=?, price=?, category=? WHERE id=? AND user_id=?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$data['name'], $data['description'], $data['price'], $data['category'], $product_id, $user_id]);
    }
}

function deleteProduct($product_id, $user_id) {
    $db = Database::getConnection();
    $stmt = $db->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    return $stmt->execute([$product_id, $user_id]);
}

function updateUserTheme($user_id, $theme) {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE users SET selected_theme = ? WHERE id = ?");
    return $stmt->execute([$theme, $user_id]);
}
?>
