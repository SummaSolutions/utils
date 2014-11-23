$j(document).ready(function () {
    $j('.defer-image').each(function () {
        var src = $j(this).data('src');
        $j(this).removeData('src');
        if($j.trim(src) != ''){
            $j(this).attr('src', src);
        }
    });
});