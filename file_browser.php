<?php
// Define the base directory where your PDF files are stored
$baseDir = 'Accountability files/'; // Make sure this directory exists

// Function to display files and folders
function displayFiles($dir, $searchTerm = '') {
    // Open the directory
    if ($handle = opendir($dir)) {
        echo "<h2>Files in: " . htmlspecialchars($dir) . "</h2>";
        echo "<div class='file-container'>";

        $foundFiles = false; // Flag to check if any files are found

        // Loop through the directory
        while (false !== ($entry = readdir($handle))) {
            // Skip the current and parent directory entries
            if ($entry != "." && $entry != "..") {
                // If it's a directory, create a card for it
                if (is_dir($dir . $entry)) {
                    echo "<div class='file-card folder'><a href='?dir=" . urlencode($dir . $entry) . "'>" . htmlspecialchars($entry) . "</a></div>";
                } 
                // If it's a PDF file, create a card to view it
                elseif (pathinfo($entry, PATHINFO_EXTENSION) === 'pdf') {
                    // Check if the search term is present in the file name
                    if (empty($searchTerm) || stripos($entry, $searchTerm) !== false) {
                        echo "<div class='file-card file'>";
                        echo "<a href='view_pdf.php?file=" . urlencode($dir . $entry) . "'>" . htmlspecialchars($entry) . "</a>";
                        echo "<iframe class='pdf-preview' src='" . htmlspecialchars($dir . $entry) . "'></iframe>";
                        echo "</div>";
                        $foundFiles = true; // Set flag to true if a file is found
                    }
                }
            }
        }

        // If no files were found, display a message
        if (!$foundFiles) {
            echo "<p class='error'>No results found for '" . htmlspecialchars($searchTerm) . "'.</p>";
        }

        echo "</div>";
        closedir($handle);
    } else {
        echo "<p class='error'>Unable to open directory.</p>";
    }
}

// Determine the current directory
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : $baseDir;

// Ensure the directory is within the base directory
if (strpos(realpath($currentDir), realpath($baseDir)) !== 0) {
    die("Access denied.");
}

// Get the search term from the query string
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Display the files and folders
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Browser</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS file -->
	<link rel="icon" type="image/x-icon" href="logo small.png">
	    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<button type="button" class="btn btn-danger" onclick="window.location.href='AF.php';">
                        <i class="fas fa-file-alt"></i> Back
                    </button>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #333;
            text-align: center;
        }
        .file-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        .file-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 10px;
            padding: 10px;
            text-align: center;
            width: 220px;
            transition: transform 0.2s;
        }
        .file-card:hover {
            transform: scale(1.05);
        }
        .file-card a {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        .file-card.folder {
            background-color: #d1ecf1;
        }
        .file-card.folder a {
            color: #0c5460;
        }
        .pdf-preview {
            width: 100%;
            height: 150px; /* Increased height for the preview */
            border: none;
            border-radius: 5px;
            margin-top: 5px }
        .error {
            color: red;
            text-align: center;
        }
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .search-bar input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
        }
        .search-bar button {
            padding: 10px;
            border: none;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-bar button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="search-bar">
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by file name..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        <input type="hidden" name="dir" value="<?php echo htmlspecialchars($currentDir); ?>">
        <button type="submit">Search</button>
    </form>
</div>

<?php displayFiles($currentDir, $searchTerm); ?>

</body>
</html>