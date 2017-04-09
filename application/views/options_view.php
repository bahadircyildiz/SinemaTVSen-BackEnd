<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
    <title>Document</title>
</head>
<body>
    <div class="container">
        <h2>Parse Excel</h2>
        <?php echo form_open_multipart('ExcelHandler/parse_excel');?>
        <div class="form-group">
            <label for="spreadsheet">Load Excel</label>
            <input type="file" class="form-control-file" name="spreadsheet" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>
        <button type="submit" class="btn btn-default">Gönder</button>

        <?php echo form_close(); ?>

        <?php
        if(isset($data)){
            $CI =& get_instance();
            $CI->load->library('table');
            $template = array(
                'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="table">'
            );
            $CI->table->set_template($template);
            foreach ($data as $d_key => $d_value) {
                $CI->table->set_caption($d_key);
                $headings = array();
                foreach($d_value[0] as $h_key => $heading) $headings[] = $h_key;
                $CI->table->set_heading($headings);
                echo $CI->table->generate($d_value);
            }
        }
        ?>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js" type="text/javascript"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>

</html>
