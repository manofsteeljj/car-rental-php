<?php
// Initialize database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
requireAdmin();

// Handle category form submissions
$errors = [];
$success = false;
$editCategory = null;

// Handle add/edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $icon = isset($_POST['icon']) ? sanitize($_POST['icon']) : '';
    
    // Validate form data
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    
    // If no errors, save the category
    if (empty($errors)) {
        try {
            if ($category_id > 0) {
                // Update existing category
                $query = "
                    UPDATE categories SET
                        name = :name,
                        description = :description,
                        icon = :icon,
                        updated_at = NOW()
                    WHERE id = :id
                ";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':icon' => $icon,
                    ':id' => $category_id
                ]);
                
                $_SESSION['success'] = "Category updated successfully.";
            } else {
                // Add new category
                $query = "
                    INSERT INTO categories (name, description, icon, created_at)
                    VALUES (:name, :description, :icon, NOW())
                ";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':icon' => $icon
                ]);
                
                $_SESSION['success'] = "Category added successfully.";
            }
            
            // Redirect to refresh the page
            header("Location: /admin/categories.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Handle delete category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    try {
        // Check if category exists
        $checkStmt = $pdo->prepare("SELECT id FROM categories WHERE id = :id");
        $checkStmt->execute([':id' => $category_id]);
        
        if ($checkStmt->rowCount() > 0) {
            // Check if there are cars in this category
            $carCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE category_id = :category_id");
            $carCheckStmt->execute([':category_id' => $category_id]);
            $hasCars = $carCheckStmt->fetchColumn() > 0;
            
            if ($hasCars) {
                $_SESSION['error'] = "Cannot delete category as it has associated cars.";
            } else {
                // Delete the category
                $deleteStmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
                $deleteStmt->execute([':id' => $category_id]);
                
                $_SESSION['success'] = "Category deleted successfully.";
            }
        } else {
            $_SESSION['error'] = "Category not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
    }
    
    // Redirect to refresh the page
    header("Location: /admin/categories.php");
    exit();
}

// Handle edit request
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute([':id' => $edit_id]);
    $editCategory = $stmt->fetch();
    
    if (!$editCategory) {
        $_SESSION['error'] = "Category not found.";
        header("Location: /admin/categories.php");
        exit();
    }
}

// Get all categories
$categories = getCategories();

// Include admin header
include_once __DIR__ . '/includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><?= $editCategory ? 'Edit Category' : 'Category Management' ?></h1>
        <?php if (!$editCategory): ?>
            <button type="button" class="btn btn-primary" id="add-category-btn">
                <i class="fas fa-plus"></i> Add New Category
            </button>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Category Form -->
    <div class="admin-form-container" id="category-form" <?= (!$editCategory && empty($errors)) ? 'style="display: none;"' : '' ?>>
        <form action="/admin/categories.php" method="POST" class="admin-form">
            <h2><?= $editCategory ? 'Edit Category' : 'Add New Category' ?></h2>
            
            <?php if ($editCategory): ?>
                <input type="hidden" name="category_id" value="<?= $editCategory['id'] ?>">
            <?php endif; ?>
            
            <div class="form-section">
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" name="name" id="name" required value="<?= $editCategory ? htmlspecialchars($editCategory['name']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3"><?= $editCategory ? htmlspecialchars($editCategory['description']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="icon">Icon Class (Font Awesome)</label>
                    <input type="text" name="icon" id="icon" placeholder="e.g. fas fa-car" value="<?= $editCategory ? htmlspecialchars($editCategory['icon']) : 'fas fa-car' ?>">
                    <p class="form-help">Enter a Font Awesome icon class. Default: fas fa-car</p>
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="save_category" class="btn btn-primary">
                    <?= $editCategory ? 'Update Category' : 'Add Category' ?>
                </button>
                <a href="/admin/categories.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
    
    <!-- Categories List -->
    <div class="data-card">
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>No categories found</h3>
                <p>Click the "Add New Category" button to create your first category.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Icon</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Cars</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td><i class="<?= htmlspecialchars($category['icon'] ?: 'fas fa-car') ?>"></i></td>
                                <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                <td><?= htmlspecialchars($category['description']) ?></td>
                                <td>
                                    <?php
                                    // Get count of cars in this category
                                    $countQuery = "SELECT COUNT(*) FROM cars WHERE category_id = :category_id";
                                    $countStmt = $pdo->prepare($countQuery);
                                    $countStmt->bindValue(':category_id', $category['id'], PDO::PARAM_INT);
                                    $countStmt->execute();
                                    $carCount = $countStmt->fetchColumn();
                                    ?>
                                    <?= $carCount ?> cars
                                </td>
                                <td class="actions">
                                    <a href="/admin/categories.php?edit=<?= $category['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="/admin/categories.php" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                        <button type="submit" name="delete_category" class="btn btn-sm btn-danger" <?= $carCount > 0 ? 'disabled' : '' ?> title="<?= $carCount > 0 ? 'Cannot delete category with cars' : 'Delete category' ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <a href="/pages/categories.php?id=<?= $category['id'] ?>" target="_blank" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Toggle category form visibility
    document.addEventListener('DOMContentLoaded', function() {
        const addBtn = document.getElementById('add-category-btn');
        const categoryForm = document.getElementById('category-form');
        
        if (addBtn && categoryForm) {
            addBtn.addEventListener('click', function() {
                categoryForm.style.display = 'block';
                document.querySelector('#category-form input[name="name"]').focus();
            });
        }
    });
</script>

<?php
// Include admin footer
include_once __DIR__ . '/includes/footer.php';
?>
