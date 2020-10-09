<html>
<body>
<form enctype="multipart/form-data" id="uploadform" action="/api/put-existing" method="POST">
{{ csrf_field() }}
                        <input class="button button-primary" type="file" name="file"></input>
                        <input type="submit"><br />
                    </form>
</body>
</html>