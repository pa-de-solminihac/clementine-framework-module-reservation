<?php
$this->getParentBlock($data, $request);
if ($request->ACT != "index") {
    $this->getBlock('fullcalendarresa/updatehoraire', $data, $request);
}
