<form method="post" action="book_save.php" class="form" id="save-form">
    <input type="hidden" name="id" value="<?=$book->id?>">
    <label>Title:</label>
    <div class="input-group">
        <input type="text" name="title" value="<?=htmlspecialchars($book->title)?>" class="form-control">
        <a class="input-group-addon btn"
           onclick="document.getElementById('save-form').submit(); return false;">Save</a>
    </div>
</form>

<h3>Author(s) for this book</h3>
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Name</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($bookAuthors as $author) : ?>
        <tr>
            <td><?=htmlspecialchars($author["name"], ENT_QUOTES)?></td>
            <td>
                <a href="book_author_delete.php?book_id=<?=$book->id?>&amp;author_id=<?=$author["id"]?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Add author:</h3>
<form method="post" action="book_author_add.php" class="form" id="add-author-form">
    <input type="hidden" name="book_id" value="<?=$book->id?>">
    <div class="input-group">
        <select class="form-control" name="author_id">
            <option>Select author</option>
            <?php foreach ($allAuthors as $id => $name) : ?>
                <option value="<?=$id?>"><?=htmlspecialchars($name, ENT_QUOTES)?></option>
            <?php endforeach; ?>
        </select>
        <a class="input-group-addon btn"
           onclick="document.getElementById('add-author-form').submit(); return false;">Save</a>
    </div>
</form>

<h3>This book in library</h3>
<p>
    <a href="edition_edit.php?book_id=<?=$book->id?>">Add new edition</a>
</p>
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Year</th>
        <th>ISBN</th>
        <th>Instance count</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($bookEditions as $edition) : ?>
        <tr>
            <td><?=$edition["year"]?></td>
            <td><?=htmlspecialchars($edition["isbn"], ENT_QUOTES)?></td>
            <td><?=$edition["instance_count"]?></td>
            <td>
                <a href="edition_edit.php?id=<?=$edition["id"]?>">Edit</a> |
                <a href="edition_delete.php?id=<?=$edition["id"]?>?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>