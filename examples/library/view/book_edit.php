<form method="post" action="book_save.php" class="form" id="save-form">
    <input type="hidden" name="id" value="<?=$book->id?>">
    <label>Title:</label>
    <div class="input-group">
        <input type="text" name="title" value="<?=htmlspecialchars($book->title)?>" class="form-control">
        <a class="input-group-addon btn"
           onclick="document.getElementById('save-form').submit(); return false;">Save</a>
    </div>
</form>