<form method="post" action="author_save.php" class="form" id="save-form">
    <input type="hidden" name="id" value="<?=$author->id?>">

    <label>Name:</label>
    <input type="text" name="name" value="<?=htmlspecialchars($author->name)?>" class="form-control"
           placeholder="Enter some name">

    <br>
    <input type="submit" class="btn btn-primary">
</form>