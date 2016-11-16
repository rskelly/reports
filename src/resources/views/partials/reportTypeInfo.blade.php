<div class="help-block" data-ng-if="form.wantsCombination('annotations', 'basic')">
    The basic annotation report contains graphical plots of abundances of the different annotation labels (as PDF). See the manual for the <a target="_blank" href="{{route('manual-tutorials', ['export', 'reports-schema'])}}#annotation-basic-report">report schema</a>.
</div>
<div class="help-block ng-cloak" data-ng-if="form.wantsCombination('annotations', 'extended')">
    The extended annotation report lists the abundances of annotation labels for each image (as XLSX). See the manual for the <a target="_blank" href="{{route('manual-tutorials', ['export', 'reports-schema'])}}#annotation-extended-report">report schema</a>.
</div>
<div class="help-block ng-cloak" data-ng-if="form.wantsCombination('annotations', 'full')">
    The full annotation report lists the labels, shape and coordinates of all annotations (as XLSX). See the manual for the <a target="_blank" href="{{route('manual-tutorials', ['export', 'reports-schema'])}}#annotation-full-report">report schema</a>.
</div>
<div class="help-block ng-cloak" data-ng-if="form.wantsCombination('annotations', 'csv')">
    The CSV annotation report is intended for subsequent processing and lists the annotation labels at the highest possible resolution (as CSV files in a ZIP archive). See the manual for the <a target="_blank" href="{{route('manual-tutorials', ['export', 'reports-schema'])}}#annotation-csv-report">report schema</a>.
</div>
<div class="help-block ng-cloak" data-ng-if="form.wantsCombination('annotations', 'area')">
    The annotation area report lists all rectangle, circle or polygon annotations with their dimensions and area in pixels (as XLSX). If a laserpoint detection was performed, the dimensions in m and area in m² is included, too. See the manual for the <a target="_blank" href="{{route('manual-tutorials', ['export', 'reports-schema'])}}#annotation-area-report">report schema</a>.
</div>
<div class="help-block ng-cloak" data-ng-if="form.wantsCombination('image-labels', 'basic')">
    The basic image label report lists the image labels of all images (as XLSX). See the manual for the <a target="_blank" href="{{route('manual-tutorials', ['export', 'reports-schema'])}}#image-label-basic-report">report schema</a>.
</div>
<div class="help-block ng-cloak" data-ng-if="form.wantsCombination('image-labels', 'csv')">
    The CSV image label report is intended for subsequent processing and lists the image labels at the highest possible resolution (as CSV files in a ZIP archive). See the manual for the <a target="_blank" href="{{route('manual-tutorials', ['export', 'reports-schema'])}}#image-label-csv-report">report schema</a>.
</div>