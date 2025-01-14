<!-- Name:Tanzilur Rahman -->
<!-- Student ID:200595789 -->

<?php
session_start(); // Start a session to manage user data across requests

// Function to load products from a CSV file into an associative array
function loadProductsFromCSV($csvFile)
{
    $products = []; // Initialize an empty array to hold products
    if (($handle = fopen($csvFile, "r")) !== false) { // Open the CSV file for reading
        $header = fgetcsv($handle); // Read the first row as header (skipping it)
        while (($row = fgetcsv($handle)) !== false) { // Read each row from the CSV file
            $products[trim($row[0])] = [ // Use the first column (barcode) as the key
                'name' => $row[1], // Second column is the product name
                'price' => (float)$row[2], // Third column is the price (convert to float)
                'category' => $row[3] // Fourth column is the category
            ];
        }
        fclose($handle); // Close the file after reading
    }
    return $products; // Return the associative array of products
}

// Function to display a table of scanned items
function displayTable($items)
{
    if (empty($items)) { // Check if there are no scanned items
        echo "<p style='text-align: center;'>No items scanned yet.</p>"; // Display a message
        return;
    }

    // Start building the HTML table
    echo "<table>";
    echo "<tr><th>Barcode</th><th>Name</th><th>Price ($)</th><th>Category</th><th>Quantity</th><th>Subtotal ($)</th><th>Actions</th></tr>";
    $totalPrice = 0; // Initialize total price

    foreach ($items as $barcode => $item) { // Loop through each scanned item
        $subtotal = $item['price'] * $item['quantity']; // Calculate the subtotal for the item
        $totalPrice += $subtotal; // Add subtotal to the total price

        // Display the item's details in a table row
        echo "<tr>
                <td>{$barcode}</td>
                <td>{$item['name']}</td>
                <td>{$item['price']}</td>
                <td>{$item['category']}</td>
                <td>{$item['quantity']}</td>
                <td>{$subtotal}</td>
                <td>
                    <!-- Button to void the item -->
                    <form method='POST' style='display: inline;'>
                        <input type='hidden' name='void_item' value='{$barcode}'>
                        <button type='submit'>Void</button>
                    </form>
                    <!-- Button to increment the item's quantity -->
                    <form method='POST' style='display: inline;'>
                        <input type='hidden' name='increment_item' value='{$barcode}'>
                        <button type='submit'>+</button>
                    </form>
                    <!-- Button to decrement the item's quantity -->
                    <form method='POST' style='display: inline;'>
                        <input type='hidden' name='decrement_item' value='{$barcode}'>
                        <button type='submit'>-</button>
                    </form>
                </td>
              </tr>";
    }

    // Display the total price in the last row
    echo "<tr>
            <td colspan='5'><strong>Total</strong></td>
            <td colspan='2'><strong>\${$totalPrice}</strong></td>
          </tr>";
    echo "</table>";

    // Display the total price in a hidden input field for checkout
    echo "<form method='POST' style='text-align: center; margin-top: 20px;'>
            <input type='hidden' name='total_price' value='{$totalPrice}'>
            <button type='submit' name='checkout' style='padding: 10px 20px; font-size: 1rem; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>Checkout</button>
          </form>";
}

// Main logic starts here
$csvFile = "SuperShopItems.csv"; // Path to the CSV file containing product data
$products = loadProductsFromCSV($csvFile); // Load products into an array

// Initialize session storage for scanned items
if ($_SERVER["REQUEST_METHOD"] === "POST") { // Check if the request is a POST request
    // Reset all scanned items
    if (isset($_POST['reset'])) {
        $_SESSION['scannedItems'] = []; // Clear the scanned items from the session
    }

    // Add a product by its barcode
    if (!empty($_POST['barcode'])) {
        $barcode = trim($_POST['barcode']); // Get the barcode from the form input
        if (isset($products[$barcode])) { // Check if the barcode exists in the products array
            if (isset($_SESSION['scannedItems'][$barcode])) {
                $_SESSION['scannedItems'][$barcode]['quantity']++; // Increment quantity if already scanned
            } else {
                $_SESSION['scannedItems'][$barcode] = $products[$barcode]; // Add new item to scanned items
                $_SESSION['scannedItems'][$barcode]['quantity'] = 1; // Set initial quantity to 1
            }
        } else {
            echo "<p style='text-align: center; font-size:1.5em; font-weight:500; color: red;'>Product with barcode '{$barcode}' not found.</p>"; // Show error message if barcode not found
        }
    }

    // Void an item completely
    if (isset($_POST['void_item'])) {
        $barcode = $_POST['void_item']; // Get the barcode of the item to void
        unset($_SESSION['scannedItems'][$barcode]); // Remove the item from scanned items
    }

    // Increment item quantity
    if (isset($_POST['increment_item'])) {
        $barcode = $_POST['increment_item']; // Get the barcode of the item to increment
        if (isset($_SESSION['scannedItems'][$barcode])) {
            $_SESSION['scannedItems'][$barcode]['quantity']++; // Increase the item's quantity
        }
    }

    // Decrement item quantity
    if (isset($_POST['decrement_item'])) {
        $barcode = $_POST['decrement_item']; // Get the barcode of the item to decrement
        if (isset($_SESSION['scannedItems'][$barcode]) && $_SESSION['scannedItems'][$barcode]['quantity'] > 1) {
            $_SESSION['scannedItems'][$barcode]['quantity']--; // Decrease the item's quantity
        }
    }

    // Handle checkout
    if (isset($_POST['checkout'])) {
        $totalPrice = $_POST['total_price']; // Get the total price
        echo "<p style='text-align: center; color: green; font-size: 1.5rem;'>Thank you for shopping! Your final bill is \${$totalPrice}.</p>";
        $_SESSION['scannedItems'] = []; // Clear the scanned items after checkout
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Shop</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin: 20px 0;
            color: #444;
            font-size: 2.5rem;
        }
        h2 {
            text-align: center;
            margin: 10px 0;
            color: #666;
            font-size: 1.5rem;
        }
        .forms {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        form {
            margin: 0 10px;
        }
        input[type="text"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 250px;
            font-size: 1rem;
        }
        button {
            padding: 10px 15px;
            font-size: 1rem;
            color: white;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease 2s;
        }
        #reset-button{
            margin-left: 10px;
            background-color: red;
            transition: background-color 0.3s ease;
        }
        #reset-button:hover{
            background-color: rgba(0,255,0 ,1.5);
        }
        button:hover {
            background-color: #0056b3;
        }
        button:active,#reset-button:active{
            background-color: #333;
        }
        button[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th {
            background-color: #007BFF;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 0.9rem;
        }
        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        td {
            font-size: 0.95rem;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f7ff;
        }
        .actions form {
            display: inline;
        }
        .actions button {
            margin: 0 2px;
            padding: 5px 10px;
            font-size: 0.9rem;
            border-radius: 3px;
        }
        .actions button:nth-child(1) {
            background-color: #ff4d4d;
        }
        .actions button:nth-child(1):hover {
            background-color: #cc0000;
        }
        .actions button:nth-child(2) {
            background-color: #28a745;
        }
        .actions button:nth-child(2):hover {
            background-color: #218838;
        }
        .actions button:nth-child(3) {
            background-color: #ffc107;
        }
        .actions button:nth-child(3):hover {
            background-color: #e0a800;
        }
        p {
            text-align: center;
            font-size: 1rem;
            color: #555;
        }
        
    </style>
</head>
<body>
    <h1>Welcome to Super Shop</h1>
    <h2>Scan and Manage Your Products</h2>
    <div class="forms">
        <form method="POST" style="display: inline;">
            <label for="barcode">Enter Barcode:</label>
            <input type="text" name="barcode" id="barcode" required>
            <button type="submit">Add Product</button>
        </form>

        <form method="POST" style="display: inline;">
            <button id="reset-button" type="submit" name="reset" value="1">Reset</button>
        </form>
    </div>

    <?php
    // Display the table of scanned items
    displayTable($_SESSION['scannedItems']);
    ?>
</body>
</html>
