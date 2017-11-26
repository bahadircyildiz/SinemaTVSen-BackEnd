<div class="container">
    <? echo form_open("MainMenu/AppSettings");?>
        <div class="form-group">
            <label for="slideElements">Slide Elements Query Selector</label>
            <textarea class="form-control" name="slideElements" id="slideElements"><?= $slideElements ?></textarea>
        </div>
        <div class="form-group">
            <label for="imageElements">Image Elements Query Selector</label>
            <textarea class="form-control" name="imageElements" id="imageElements"><?= $imageElements ?></textarea>
        </div>
        <div class="form-group">
            <label for="linkElements">Link Elements Query Selector</label>
            <textarea class="form-control" name="linkElements" id="linkElements"><?= $linkElements ?></textarea>
        </div>
        <div class="form-group">
            <label for="emptyElements">Empty Elements Query Selector</label>
            <textarea class="form-control" name="emptyElements" id="emptyElements"><?= $emptyElements ?></textarea>
        </div>
        <div class="form-group">
            <label for="sideMenuContents">Side Menu Contents</label>
            <textarea class="form-control" name="sideMenuContents" id="sideMenuContents"><?= $sideMenuContents ?></textarea>
        </div>
        <button type="submit" class="btn btn-default">Send</button>
    <? echo form_close();?>
    <? if(isset($save_result)) { ?>
        <div class="alert alert-success" role="alert"> <?= $save_result ?> Ayar Güncellendi. </div>
    <? } else if (isset($error_code)) { ?>
        <div class="alert alert-warning" role="alert"> <span>Hata!</span> <?= $error_code?>: <?= $error_message?> </div>
    <? }?>
    <!-- </form>  -->
</div>