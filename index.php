<?php
// index.php - Smart Shelf Dashboard
require_once 'db.php';

// Handle DELETE item
if (isset($_GET['delete_item'])) {
    $id = intval($_GET['delete_item']);
    $conn->query("DELETE FROM items WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Handle DELETE shelf
if (isset($_GET['delete_shelf'])) {
    $id = intval($_GET['delete_shelf']);
    $conn->query("DELETE FROM shelves WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Handle ADD SHELF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_shelf'])) {
    $name = $conn->real_escape_string($_POST['shelf_name']);
    $location = $conn->real_escape_string($_POST['shelf_location']);
    $conn->query("INSERT INTO shelves (name, location) VALUES ('$name', '$location')");
    header("Location: index.php");
    exit();
}

// Handle ADD ITEM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $shelf_id  = intval($_POST['shelf_id']);
    $name      = $conn->real_escape_string($_POST['item_name']);
    $category  = $conn->real_escape_string($_POST['category']);
    $quantity  = intval($_POST['quantity']);
    $min_qty   = intval($_POST['min_quantity']);
    $unit      = $conn->real_escape_string($_POST['unit']);
    $price     = floatval($_POST['price']);
    $conn->query("INSERT INTO items (shelf_id, name, category, quantity, min_quantity, unit, price)
                  VALUES ($shelf_id, '$name', '$category', $quantity, $min_qty, '$unit', $price)");
    header("Location: index.php");
    exit();
}

// Handle UPDATE ITEM QUANTITY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $id  = intval($_POST['item_id']);
    $qty = intval($_POST['new_qty']);
    $conn->query("UPDATE items SET quantity = $qty WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Stats
$total_items   = $conn->query("SELECT COUNT(*) as c FROM items")->fetch_assoc()['c'];
$total_shelves = $conn->query("SELECT COUNT(*) as c FROM shelves")->fetch_assoc()['c'];
$low_stock     = $conn->query("SELECT COUNT(*) as c FROM items WHERE quantity <= min_quantity")->fetch_assoc()['c'];
$total_value   = $conn->query("SELECT SUM(quantity * price) as v FROM items")->fetch_assoc()['v'];

// All shelves with items
$shelves = $conn->query("SELECT * FROM shelves ORDER BY id");
$all_shelves_dropdown = $conn->query("SELECT id, name FROM shelves ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Shelf System</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Segoe UI', sans-serif;
    background: #f0f4f8;
    color: #1a202c;
  }

  /* HEADER */
  header {
    background: linear-gradient(135deg, #1a3c2e, #2d6a4f);
    color: white;
    padding: 18px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  }
  header h1 { font-size: 1.6rem; letter-spacing: 1px; }
  header span { font-size: 0.9rem; opacity: 0.8; }

  .container { max-width: 1200px; margin: 0 auto; padding: 25px 20px; }

  /* STAT CARDS */
  .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
  .stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    border-top: 4px solid #2d6a4f;
  }
  .stat-card.red  { border-top-color: #e53e3e; }
  .stat-card.blue { border-top-color: #3182ce; }
  .stat-card.gold { border-top-color: #d69e2e; }
  .stat-card h2 { font-size: 2rem; font-weight: 700; color: #2d6a4f; }
  .stat-card.red  h2 { color: #e53e3e; }
  .stat-card.blue h2 { color: #3182ce; }
  .stat-card.gold h2 { color: #d69e2e; }
  .stat-card p { font-size: 0.85rem; color: #718096; margin-top: 4px; }

  /* SECTION TITLES */
  .section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 14px;
    padding-bottom: 6px;
    border-bottom: 2px solid #2d6a4f;
    display: inline-block;
  }

  /* FORMS */
  .forms-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px; }
  .form-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
  .form-row.triple { grid-template-columns: 1fr 1fr 1fr; }
  label { font-size: 0.78rem; color: #4a5568; font-weight: 600; display: block; margin-bottom: 3px; }
  input, select {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #cbd5e0;
    border-radius: 6px;
    font-size: 0.9rem;
    color: #2d3748;
    background: #f7fafc;
    outline: none;
    transition: border 0.2s;
  }
  input:focus, select:focus { border-color: #2d6a4f; background: white; }
  .btn {
    padding: 9px 18px;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
  }
  .btn:hover { opacity: 0.85; }
  .btn-green  { background: #2d6a4f; color: white; }
  .btn-red    { background: #e53e3e; color: white; font-size: 0.78rem; padding: 5px 10px; }
  .btn-blue   { background: #3182ce; color: white; font-size: 0.78rem; padding: 5px 10px; }
  .btn-full   { width: 100%; margin-top: 8px; }

  /* LOW STOCK ALERT */
  .alert-banner {
    background: #fff5f5;
    border: 1px solid #fc8181;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    color: #c53030;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* SHELF CARDS */
  .shelves-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 20px; }
  .shelf-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    overflow: hidden;
  }
  .shelf-header {
    background: linear-gradient(90deg, #1a3c2e, #2d6a4f);
    color: white;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .shelf-header h3 { font-size: 1rem; }
  .shelf-header small { font-size: 0.78rem; opacity: 0.8; }
  .shelf-del { background: rgba(255,255,255,0.15); border: none; color: white;
               padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 0.78rem; }
  .shelf-del:hover { background: rgba(229,62,62,0.7); }

  /* ITEMS TABLE */
  table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
  th { background: #f7fafc; color: #4a5568; font-weight: 700;
       padding: 8px 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
  td { padding: 7px 10px; border-bottom: 1px solid #f0f4f8; vertical-align: middle; }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: #f7fafc; }

  /* STOCK BADGE */
  .badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
  }
  .badge-green  { background: #c6f6d5; color: #276749; }
  .badge-yellow { background: #fefcbf; color: #744210; }
  .badge-red    { background: #fed7d7; color: #9b2335; }

  /* INLINE UPDATE FORM */
  .qty-form { display: flex; gap: 5px; align-items: center; }
  .qty-form input { width: 60px; padding: 4px 6px; }

  .no-items { padding: 15px; text-align: center; color: #a0aec0; font-size: 0.85rem; }

  footer {
    text-align: center;
    padding: 20px;
    color: #a0aec0;
    font-size: 0.8rem;
    margin-top: 30px;
  }

  @media (max-width: 768px) {
    .stats { grid-template-columns: repeat(2, 1fr); }
    .forms-row { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
    .form-row.triple { grid-template-columns: 1fr; }
    .shelves-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<header>
  <h1>📦 Smart Shelf System</h1>
  <span>Inventory & Stock Management</span>
</header>

<div class="container">

  <!-- STATS -->
  <div class="stats">
    <div class="stat-card blue">
      <h2><?= $total_shelves ?></h2>
      <p>Total Shelves</p>
    </div>
    <div class="stat-card">
      <h2><?= $total_items ?></h2>
      <p>Total Items</p>
    </div>
    <div class="stat-card red">
      <h2><?= $low_stock ?></h2>
      <p>Low Stock Items</p>
    </div>
    <div class="stat-card gold">
      <h2>₹<?= number_format($total_value, 0) ?></h2>
      <p>Total Stock Value</p>
    </div>
  </div>

  <!-- LOW STOCK ALERT -->
  <?php if ($low_stock > 0): ?>
  <div class="alert-banner">
    ⚠️ <strong><?= $low_stock ?> item(s)</strong> are at or below minimum stock level. Please restock soon!
  </div>
  <?php endif; ?>

  <!-- ADD FORMS -->
  <div class="forms-row">

    <!-- Add Shelf -->
    <div class="form-card">
      <div class="section-title">➕ Add New Shelf</div>
      <form method="POST">
        <div class="form-row">
          <div>
            <label>Shelf Name</label>
            <input type="text" name="shelf_name" placeholder="e.g. Shelf D" required>
          </div>
          <div>
            <label>Location</label>
            <input type="text" name="shelf_location" placeholder="e.g. Aisle 4">
          </div>
        </div>
        <button type="submit" name="add_shelf" class="btn btn-green btn-full">Add Shelf</button>
      </form>
    </div>

    <!-- Add Item -->
    <div class="form-card">
      <div class="section-title">📋 Add New Item</div>
      <form method="POST">
        <div class="form-row">
          <div>
            <label>Select Shelf</label>
            <select name="shelf_id" required>
              <option value="">-- Select Shelf --</option>
              <?php
                $all_shelves_dropdown->data_seek(0);
                while ($s = $all_shelves_dropdown->fetch_assoc()):
              ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div>
            <label>Item Name</label>
            <input type="text" name="item_name" placeholder="e.g. Milk" required>
          </div>
        </div>
        <div class="form-row">
          <div>
            <label>Category</label>
            <input type="text" name="category" placeholder="e.g. Dairy">
          </div>
          <div>
            <label>Unit</label>
            <select name="unit">
              <option value="pcs">pcs</option>
              <option value="kg">kg</option>
              <option value="litre">litre</option>
              <option value="box">box</option>
              <option value="pack">pack</option>
            </select>
          </div>
        </div>
        <div class="form-row triple">
          <div>
            <label>Quantity</label>
            <input type="number" name="quantity" placeholder="0" min="0" required>
          </div>
          <div>
            <label>Min. Stock</label>
            <input type="number" name="min_quantity" placeholder="5" min="1" required>
          </div>
          <div>
            <label>Price (₹)</label>
            <input type="number" name="price" placeholder="0.00" step="0.01" min="0">
          </div>
        </div>
        <button type="submit" name="add_item" class="btn btn-green btn-full">Add Item to Shelf</button>
      </form>
    </div>

  </div><!-- end forms-row -->

  <!-- SHELVES & ITEMS -->
  <div class="section-title">🗄️ All Shelves & Items</div>
  <div class="shelves-grid" style="margin-top:14px;">
    <?php
    $shelves->data_seek(0);
    while ($shelf = $shelves->fetch_assoc()):
      $sid   = $shelf['id'];
      $items = $conn->query("SELECT * FROM items WHERE shelf_id = $sid ORDER BY name");
    ?>
    <div class="shelf-card">
      <div class="shelf-header">
        <div>
          <h3>📦 <?= htmlspecialchars($shelf['name']) ?></h3>
          <small>📍 <?= htmlspecialchars($shelf['location'] ?: 'No location') ?></small>
        </div>
        <a href="?delete_shelf=<?= $sid ?>"
           onclick="return confirm('Delete shelf and ALL its items?')"
           class="shelf-del">✕ Delete</a>
      </div>

      <?php if ($items->num_rows === 0): ?>
        <div class="no-items">No items on this shelf yet.</div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th>Category</th>
            <th>Qty</th>
            <th>Status</th>
            <th>Price</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($item = $items->fetch_assoc()):
          $qty     = $item['quantity'];
          $min_qty = $item['min_quantity'];
          if ($qty <= 0)           { $badge = 'badge-red';    $status = '❌ Out'; }
          elseif ($qty <= $min_qty){ $badge = 'badge-yellow'; $status = '⚠️ Low'; }
          else                     { $badge = 'badge-green';  $status = '✅ OK'; }
        ?>
          <tr>
            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
            <td><?= htmlspecialchars($item['category']) ?></td>
            <td>
              <!-- Inline quantity update -->
              <form method="POST" class="qty-form">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <input type="number" name="new_qty" value="<?= $qty ?>" min="0" style="width:55px;">
                <button type="submit" name="update_qty" class="btn btn-blue">↺</button>
              </form>
            </td>
            <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
            <td>₹<?= number_format($item['price'], 2) ?><br><small><?= $item['unit'] ?></small></td>
            <td>
              <a href="?delete_item=<?= $item['id'] ?>"
                 onclick="return confirm('Delete this item?')"
                 class="btn btn-red">✕</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    <?php endwhile; ?>
  </div>

</div><!-- end container -->

<footer>Smart Shelf System &copy; <?= date('Y') ?> &mdash; Built with PHP & MySQL</footer>

</body>
</html>
