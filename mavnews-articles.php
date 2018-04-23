<h1>Mavnews</h1>
<p class="search-box">
<form method="POST">
	<label class="screen-reader-text" for="post-search-input">Search Wires:</label>
	<input type="search" id="post-search-input" name="s" value="" required>
	<input type="submit" id="search-submit" class="button" value="Search Wires">
</form>
</p>
<?php
	if ($search) {
?>
<p>Found <strong><?= number_format($count) ?></strong> results for <em><?= $searchStr ?></em> <a href="admin.php?page=mavnews.php">Clear</p>
<?php
	}
?>
<table class="wp-list-table widefat fixed striped posts">
	<thead>
	<tr>
		<th scope="col" id="title" class="manage-column column-title column-primary desc"><span>Title</span></th>
		<th scope="col" id="author" class="manage-column column-author">Provider</th>
		<th scope="col" id="tags" class="manage-column column-tags">Keywords</th>
		<th scope="col" id="date" class="manage-column column-date asc"><span>Date</span></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
	forEach($articles as $article) {
?>
		<tr id="post-20" class="iedit level-0 post-20 type-post status-draft format-standard hentry category-uncategorized">
			<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
				<a href="post-new.php?mavnews-id=<?= $article->_id ?>&post_type=article"><?= $article->headline ?></a>
			</td>
			<td class="author column-author" data-colname="Provider"><?= $article->provider ?></td>
			<td class="tags column-tags" data-colname="Keywords"><?= implode(", ", $article->keywords) ?></td>
			<td class="date column-date" data-colname="Date">Last Modified<br><abbr title="2018/01/08 2:02:39 pm"><?= date("d M Y h:i", strtotime($article->date)) ?></abbr></td>		
		</tr>
<?php } ?>
	</tbody>

	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-title column-primary sortable desc"><a href="http://localhost:8181/wp-admin/edit.php?orderby=title&amp;order=asc"><span>Title</span><span class="sorting-indicator"></span></a></th>
		<th scope="col" class="manage-column column-author">Provider</th>
		<th scope="col" class="manage-column column-tags">Keywords</th>
		<th scope="col" class="manage-column column-date sortable asc"><span>Date</span></th>
	</tr>
	</tfoot>

</table>