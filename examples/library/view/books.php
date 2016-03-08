<h3>Add new book</h3>
<form method="post" action="book_add.php" class="form" id="add-form">
    <input type="hidden" name="command" value="add">
    <label>Title:</label>
    <div class="input-group">
        <input type="text" name="title" class="form-control">
        <a class="input-group-addon btn"
           onclick="document.getElementById('add-form').submit(); return false;">Add</a>
    </div>
</form>

<h3>Books in library</h3>
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Title</th>
        <th>Actions</th>
    </tr>
    </thead>
    <?php foreach ($books as $book) : ?>
        <tr>
            <td><?=htmlspecialchars($book["title"], ENT_QUOTES)?></td>
            <td>
                <a href="book_edit.php?id=<?=$book["id"]?>">Edit</a> |
                <a href="book_delete.php?id=<?=$book["id"]?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>