<script type="text/javascript">
    // Row-action menus inside a DataTable sit in a horizontally scrolling box, and a box that
    // scrolls on one axis clips (or adds a scrollbar for) anything that overflows the other.
    // Open these menus with fixed positioning instead, so they float over the table and flip
    // upwards when there is no room below.
    //
    // This runs while the page body is still loading, before the footer loads Bootstrap, so
    // Bootstrap is looked up when a menu is clicked, not when this script runs.
    (function () {
        // Bootstrap's own (delegated) dropdown handler runs in the capture phase on `document`,
        // so listen on `window`, which captures first, to give the dropdown its settings
        // before Bootstrap creates it.
        window.addEventListener('click', function (e) {
            var DropdownClass = window.bootstrap && window.bootstrap.Dropdown;
            var toggle = e.target.closest ? e.target.closest('.card-datatable [data-bs-toggle="dropdown"]') : null;

            if (!DropdownClass || !toggle || DropdownClass.getInstance(toggle)) { return; }

            new DropdownClass(toggle, {
                popperConfig: function (config) {
                    return Object.assign({}, config, { strategy: 'fixed' });
                }
            });
        }, true);
    })();
</script>
