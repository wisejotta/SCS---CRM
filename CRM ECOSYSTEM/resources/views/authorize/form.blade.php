<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Authorizing...</title>
</head>
<body>
    <form id="send_hptoken" action="{{ config('authorize.paymenrBaseUrl') }}" method="post">
        <input type="hidden" name="token" value="{{ $token }}" />
    </form>
    <script>document.getElementById("send_hptoken").submit();</script>
</body>
</html>