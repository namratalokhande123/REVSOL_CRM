<?php
include_once 'header.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Files</title>
    <style>
        
       .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 60%;
            position: absolute;
            /* Set the position to absolute */
            top: 50%;
            /* Position the container at 50% from the top */
            left: 50%;
            /* Position the container at 50% from the left */
            transform: translate(-50%, -50%);
            /* Center the container */
            margin: auto;
            /* Center the container horizontally */
            z-index: 1;
            /* Ensure the container appears above other elements */
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        #uploadBtn {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        #uploadBtn:hover {
            background-color: #45a049;
        }
        #summary {
            margin-top: 20px;
            font-size: 18px;
            color: #333;
        }
        .back-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #555;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Files to Client Folder</h1>
        <button id="uploadBtn">UPLOAD FILES</button>
        <div id="summary"></div>
        <button class="back-button" id="backBtn">BACK</button>
    </div>

    <script>
        document.getElementById("uploadBtn").addEventListener("click", function() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "upload_client.php", true);
            xhr.onload = function () {
                if (xhr.status == 200) {
                    // Display response from upload_client.php
                    document.getElementById("summary").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        });

            document.getElementById("backBtn").addEventListener("click", function() {
            window.location.href = "dashboard.php?page=upload_to_client";
        });
    </script>
</body>
</html>
