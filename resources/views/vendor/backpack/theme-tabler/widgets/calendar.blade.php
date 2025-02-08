@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))
<div class="{{ $widget['class'] ?? 'well mb-2' }}">
    <div class="card">
        <div class="card-header">
            <h2>Calendar</h2>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))
