<?php
if (isset($_POST['name']))
{
    var_dump(file_put_contents('folder/' . $_POST['name'],json_encode($_POST['data'])));
}
var_dump($_POST);
