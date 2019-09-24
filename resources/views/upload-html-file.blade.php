<html>
<body>
<form action="{{ route('generate-pdf') }}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <input type="file" name="file" />
    <br><br>
    <input type="submit" value=" Generate PDF " />
</form>
</body>
</html>
