<?php
include_once 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Level 1 Work</title>
    <style>
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 30%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        button, #fileLabel, #uploadBtn, .back-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin: 5px;
        }

        button:hover, #fileLabel:hover, #uploadBtn:hover, .back-button:hover {
            background-color: #45a049;
        }

        #uploadForm {
            margin-top: 20px;
            display: none;
            text-align: left;
        }

        input[type="file"] {
            display: none;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box; /* Ensures padding and border are included in the element's total width */
        }

        textarea {
            height: 100px; /* Increased height for better readability */
            resize: vertical;
        }

        label {
            font-size: 16px;
            margin-bottom: 5px;
            display: block;
            text-align: left;
        }

        /* Increased font size for better visibility */
        #pageCount {
            font-size: 18px;
        }

        .back-button {
            background-color: #555;
        }

        .back-button:hover {
            background-color: #333;
        }

        .flex-container {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .flex-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        /* Style for the form container */
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            max-width: 400px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <button onclick="getFile()">GET FILE</button>
        <button onclick="showUploadForm()">UPLOAD FILE</button>
 
            <form id="uploadForm" action="upload_file.php" method="post" enctype="multipart/form-data" class="flex-container">
            <div >
            <label for="pageCount">Page Count</label>
<input type="text" id="pageCount" name="page_count" placeholder="Enter page count" required style="font-size: 18px;">

<label for="comments">Comments</label>
<textarea id="comments" name="comments" placeholder="Enter your comments" required style="font-size: 18px;"></textarea>

    <!-- Adding the 'required' attribute to make the field mandatory -->
</div>

                <div class="flex-buttons">
                    <input type="file" id="fileInput" name="uploaded_file" required>
                    <label for="fileInput" id="fileLabel">Choose File</label>
                    <button id="uploadBtn" type="submit">Upload</button>
                    <button class="back-button" type="button" onclick="history.back()">BACK</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getFile() {
            window.location.href = 'get_file.php';
        }

        function showUploadForm() {
            document.getElementById('uploadForm').style.display = 'block';
        }


        function validateForm() {
            var pageCount = document.getElementById('pageCount').value.trim();
            var comments = document.getElementById('comments').value.trim();
            var fileInput = document.getElementById('fileInput').value.trim();

            if (pageCount === '') {
                alert('Please enter the page count.');
                return false; 
            }

            if (comments === '') {
                alert('Please enter your comments.');
                return false; 
            }

            if (fileInput === '') {
                alert('Please choose a file.');
                return false; 
            }

            return true; 
        }
   
    </script>
</body>
</html>
