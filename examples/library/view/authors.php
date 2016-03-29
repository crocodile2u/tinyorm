<h3>Add new author</h3>
<form method="post" action="author_save.php" class="form" id="add-form">
    <label>Name:</label>
    <div class="input-group">
        <input type="text" name="name" class="form-control">
        <a class="input-group-addon btn"
           onclick="document.getElementById('add-form').submit(); return false;">Add</a>
    </div>
</form>

<h3>Authors</h3>
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Name</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($authors as $author) : ?>
        <tr>
            <td><?=htmlspecialchars($author["name"], ENT_QUOTES)?></td>
            <td>
                <a href="author_edit.php?id=<?=$author["id"]?>">Edit</a> |
                <a href="author_delete.php?id=<?=$author["id"]?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>