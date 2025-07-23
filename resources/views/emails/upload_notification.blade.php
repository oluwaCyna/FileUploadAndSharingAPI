<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your files are ready for download</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1a73e8;
        }

        a.button {
            background: #1a73e8;
            color: #fff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-top: 15px;
        }

        p {
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📁 Your files are ready!</h1>
        <p>Hello,</p>
        <p>
            Your files have been successfully uploaded and are ready for download.
        </p>
        <p>
            Click the button below to download your files:
        </p>
        <p>
            <a href="{{ $downloadUrl }}" class="button">Download Files</a>
        </p>
        <p>Thank you for using our service!</p>
    </div>
</body>

</html>
