<nav>
	<label for="view-all-radio" class="tm-btn btn-default">
        <input type="radio" name="report-type" id="view-all-radio" value="all" hidden> All
    </label>
    <?php if(current_user_can('manage_options')) : ?>
        <label for="view-single-radio" class="tm-btn btn-transparent">
            <input type="radio" name="report-type" id="view-single-radio" value="single" hidden> Single user
        </label>
        <label for="view-detail-radio" class="tm-btn btn-transparent">
            <input type="radio" name="report-type" id="view-detail-radio" value="detail" hidden> Detailed
        </label>
    <?php endif; ?>
</nav>