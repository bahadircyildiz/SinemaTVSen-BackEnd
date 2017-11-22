<div class="container">
    <h2>Parse Excel</h2>
    <?php echo form_open_multipart('MainMenu/parse_excel');?>
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
        foreach ($data as $type => $content) {
            $CI->table->set_caption($type);
            foreach ($content as $tableName => $tableData) {
                $CI->table->set_caption($tableName);
                $headings = array();
                foreach($tableData[0] as $h_key => $heading) $headings[] = $h_key;
                $CI->table->set_heading($headings);
                echo $CI->table->generate($tableData);
            }
        }
    }
    ?>
</div>
