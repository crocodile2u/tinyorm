<form method="post" action="edition_save.php" class="form" id="save-form">
    <input type="hidden" name="id" value="<?=$edition->id?>">
    <input type="hidden" name="book_id" value="<?=$edition->book_id?>">

    <label>Year:</label>
    <input type="text" name="year" value="<?=htmlspecialchars($edition->year)?>" class="form-control"
           placeholder="Integer number">

    <label>ISBN:</label>
    <input type="text" name="isbn" value="<?=htmlspecialchars($edition->isbn)?>" class="form-control"
        placeholder="any string ;-)">

    <label>Instances:</label>
    <input type="text" name="instance_count" value="<?=htmlspecialchars($edition->instance_count)?>" class="form-control"
           placeholder="Integer number">

    <br>
    <input type="submit" class="btn btn-primary">
</form>