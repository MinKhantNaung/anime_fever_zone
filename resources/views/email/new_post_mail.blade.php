<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email</title>
</head>

<body>
    <div style="text-align: center">
        <img src="data:image/webp;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="anime_fever_zone_post" style="width: 100%">
    </div>
    <p style="font-size: 20px">{!! $body !!}</p>
</body>

</html>
