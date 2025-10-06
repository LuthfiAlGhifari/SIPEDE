<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include 'db.php';
$user_id = $_SESSION['user_id'];

// Get current folder ID
$current_folder = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
      padding: 20px;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e8eaed;
    }
    .logout-btn {
      background: #dc3545;
      color: white;
      padding: 8px 14px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
    }
    h2, h3 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }
    .actions {
      text-align: center;
      margin: 20px 0;
    }
    .actions button {
      padding: 10px 20px;
      background: #28a745;
      border: none;
      color: white;
      cursor: pointer;
      border-radius: 5px;
      margin: 0 10px;
    }
    .file-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    .grid-item {
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      background: #fafafa;
      transition: transform 0.3s, box-shadow 0.3s;
      cursor: pointer;
    }
    .grid-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .item-icon {
      height: 140px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f1f3f4;
    }
    .folder-icon {
      color: #fbbc04;
      font-size: 64px;
    }
    .file-icon {
      font-size: 48px;
      color: #5f6368;
    }
    .item-info {
      padding: 15px;
      border-top: 1px solid #eee;
    }
    .item-name {
      font-weight: 500;
      text-align: center;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .item-actions {
      display: flex;
      justify-content: space-around;
      padding: 10px;
      background: #f8f9fa;
      border-top: 1px solid #eee;
    }
    .action-btn {
      background: none;
      border: none;
      color: #5f6368;
      cursor: pointer;
      font-size: 16px;
      transition: color 0.3s;
    }
    .action-btn:hover {
      color: #4285f4;
    }
    .delete-btn:hover {
      color: #ea4335;
    }
    .preview-image {
      max-width: 100%;
      max-height: 140px;
      object-fit: contain;
      border-radius: 5px;
    }
    .flash-message {
      padding: 15px;
      margin: 20px 0;
      border-radius: 8px;
      text-align: center;
    }
    .success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .breadcrumbs {
      text-align: center;
      margin-bottom: 15px;
      font-size: 14px;
      color: #5f6368;
    }
    .breadcrumb a {
      color: #4285f4;
      text-decoration: none;
    }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: white;
      width: 400px;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .modal-header {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 15px;
      text-align: center;
    }
    .modal-footer {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 20px;
    }
    .modal-footer button {
      padding: 8px 16px;
      border-radius: 4px;
      border: none;
      cursor: pointer;
    }
    .modal-cancel {
      background: #f1f3f4;
      color: #202124;
    }
    .modal-submit {
      background: #4285f4;
      color: white;
    }
    input[type="text"], select {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="top-bar">
      <h2>Cloud Drive</h2>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
      <div class="flash-message success">
        <?php echo $_SESSION['success']; ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div class="flash-message error">
        <?php echo $_SESSION['error']; ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Breadcrumb Navigation -->
    <div class="breadcrumbs">
      <div class="breadcrumb">
        <a href="dashboard.php">My Drive</a>
        <?php
        if ($current_folder) {
            $folder_result = $conn->query("SELECT folder_name FROM folders WHERE id=$current_folder");
            if ($folder_result->num_rows > 0) {
                $folder_row = $folder_result->fetch_assoc();
                echo " &gt; ";
                echo "<span>" . htmlspecialchars($folder_row['folder_name']) . "</span>";
            }
        }
        ?>
      </div>
    </div>

    <div class="actions">
      <button onclick="document.getElementById('folderModal').style.display='flex'">
        <i class="fas fa-folder-plus"></i> New Folder
      </button>
      <button onclick="document.getElementById('uploadModal').style.display='flex'">
        <i class="fas fa-file-upload"></i> Upload File
      </button>
    </div>

    <!-- Folders Section -->
    <h3>Your Folders</h3>
    <div class="file-grid">
      <?php
      $folder_query = $current_folder 
          ? "SELECT * FROM folders WHERE user_id=$user_id AND parent_id=$current_folder"
          : "SELECT * FROM folders WHERE user_id=$user_id AND parent_id IS NULL";
      
      $folders = $conn->query($folder_query);
      
      if ($folders->num_rows > 0) {
          while ($folder = $folders->fetch_assoc()) {
            echo "<div class='grid-item'>";
            echo "<div class='item-icon'>";
            echo "<i class='fas fa-folder folder-icon'></i>";
            echo "</div>";
            echo "<div class='item-info'>";
            echo "<div class='item-name'>" . htmlspecialchars($folder['folder_name']) . "</div>";
            echo "</div>";
            echo "<div class='item-actions'>";
            echo "<a href='dashboard.php?folder_id={$folder['id']}' class='action-btn' title='Open'><i class='fas fa-folder-open'></i></a>";
            echo "<button class='action-btn' title='Rename' onclick='openRenameFolderModal({$folder['id']}, \"{$folder['folder_name']}\")'><i class='fas fa-edit'></i></button>";
            echo "<a href='delete_folder.php?id={$folder['id']}' class='action-btn delete-btn' title='Delete'><i class='fas fa-trash'></i></a>";
            echo "</div>";
            echo "</div>";
          }
      } else {
          echo "<p style='text-align:center; width:100%;'>No folders found</p>";
      }
      ?>
    </div>

    <!-- Files Section -->
    <h3>Your Files</h3>
    <div class="file-grid">
      <?php
      $file_query = $current_folder 
          ? "SELECT * FROM files WHERE user_id=$user_id AND folder_id=$current_folder ORDER BY uploaded_at DESC"
          : "SELECT * FROM files WHERE user_id=$user_id AND folder_id IS NULL ORDER BY uploaded_at DESC";
      
      $files = $conn->query($file_query);
      
      if ($files->num_rows > 0) {
          while ($file = $files->fetch_assoc()) {
            $is_image = strpos($file['file_type'], 'image/') === 0;
            $file_icon = getFileIcon($file['file_type']);
            
            echo "<div class='grid-item'>";
            echo "<div class='item-icon'>";
            
            if ($is_image) {
                echo "<img src='{$file['filepath']}' alt='{$file['filename']}' class='preview-image'>";
            } else {
                echo "<i class='fas $file_icon file-icon'></i>";
            }
            
            echo "</div>";
            echo "<div class='item-info'>";
            echo "<div class='item-name'>" . htmlspecialchars($file['filename']) . "</div>";
            echo "</div>";
            echo "<div class='item-actions'>";
            echo "<a href='{$file['filepath']}' download class='action-btn' title='Download'><i class='fas fa-download'></i></a>";
            echo "<button class='action-btn' title='Rename' onclick='openRenameFileModal({$file['id']}, \"{$file['filename']}\")'><i class='fas fa-edit'></i></button>";
            echo "<a href='delete.php?id={$file['id']}' class='action-btn delete-btn' title='Delete'><i class='fas fa-trash'></i></a>";
            echo "</div>";
            echo "</div>";
          }
      } else {
          echo "<p style='text-align:center; width:100%;'>No files found</p>";
      }
      
      // Helper function to get file icon
      function getFileIcon($file_type) {
        $icons = [
          'image/' => 'fa-file-image',
          'audio/' => 'fa-file-audio',
          'video/' => 'fa-file-video',
          'application/pdf' => 'fa-file-pdf',
          'application/msword' => 'fa-file-word',
          'application/vnd.ms-excel' => 'fa-file-excel',
          'application/vnd.ms-powerpoint' => 'fa-file-powerpoint',
          'text/' => 'fa-file-alt',
          'application/zip' => 'fa-file-archive',
          'default' => 'fa-file'
        ];
        
        foreach ($icons as $prefix => $icon) {
          if (strpos($file_type, $prefix) === 0) {
            return $icon;
          }
        }
        
        return $icons['default'];
      }
      ?>
    </div>
  </div>

  <!-- New Folder Modal -->
  <div class="modal" id="folderModal">
    <div class="modal-content">
      <div class="modal-header">Create New Folder</div>
      <form action="create_folder.php" method="POST">
        <input type="text" name="folder_name" placeholder="Folder Name" required>
        <input type="hidden" name="parent_id" value="<?php echo $current_folder; ?>">
        <div class="modal-footer">
          <button type="button" class="modal-cancel" onclick="document.getElementById('folderModal').style.display='none'">Cancel</button>
          <button type="submit" class="modal-submit">Create</button>
        </div>
      </form>
    </div>
  </div>

  <!-- File Upload Modal -->
  <div class="modal" id="uploadModal">
    <div class="modal-content">
      <div class="modal-header">Upload File</div>
      <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="filename" placeholder="File Name" required>
        
        <select name="folder_id">
          <option value="">-- Select Folder (optional) --</option>
          <?php
          $folders = $conn->query("SELECT * FROM folders WHERE user_id=$user_id");
          while ($folder = $folders->fetch_assoc()) {
            $selected = $folder['id'] == $current_folder ? 'selected' : '';
            echo "<option value='{$folder['id']}' $selected>{$folder['folder_name']}</option>";
          }
          ?>
        </select>
        
        <input type="file" name="file" required style="margin-top:15px;">
        
        <div class="modal-footer">
          <button type="button" class="modal-cancel" onclick="document.getElementById('uploadModal').style.display='none'">Cancel</button>
          <button type="submit" class="modal-submit">Upload</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Rename Folder Modal -->
  <div class="modal" id="renameFolderModal">
    <div class="modal-content">
      <div class="modal-header">Rename Folder</div>
      <form action="rename.php" method="POST">
        <input type="hidden" name="type" value="folder">
        <input type="hidden" name="id" id="renameFolderId">
        <input type="text" name="new_name" id="renameFolderName" required>
        <div class="modal-footer">
          <button type="button" class="modal-cancel" onclick="document.getElementById('renameFolderModal').style.display='none'">Cancel</button>
          <button type="submit" class="modal-submit">Rename</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Rename File Modal -->
  <div class="modal" id="renameFileModal">
    <div class="modal-content">
      <div class="modal-header">Rename File</div>
      <form action="rename.php" method="POST">
        <input type="hidden" name="type" value="file">
        <input type="hidden" name="id" id="renameFileId">
        <input type="text" name="new_name" id="renameFileName" required>
        <div class="modal-footer">
          <button type="button" class="modal-cancel" onclick="document.getElementById('renameFileModal').style.display='none'">Cancel</button>
          <button type="submit" class="modal-submit">Rename</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Function to open rename folder modal
    function openRenameFolderModal(id, name) {
      document.getElementById('renameFolderId').value = id;
      document.getElementById('renameFolderName').value = name;
      document.getElementById('renameFolderModal').style.display = 'flex';
    }
    
    // Function to open rename file modal
    function openRenameFileModal(id, name) {
      document.getElementById('renameFileId').value = id;
      document.getElementById('renameFileName').value = name;
      document.getElementById('renameFileModal').style.display = 'flex';
    }
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
      const modals = [
        'folderModal', 'uploadModal', 
        'renameFolderModal', 'renameFileModal'
      ];
      
      modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
    });
  </script>
</body>
</html>