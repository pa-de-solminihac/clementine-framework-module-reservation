<!-- Création de la modal et code associé -->
<a href="#" id="Modal" class="btn btn-default" data-toggle="modal" data-target="#videoModal" ></a>
<?php
$ressource_id = $request->get('int', 'clementine_reservation_ressource-id');
$id_ressource = $request->get('int', 'id_ressource');
if ($request->ACT != "all" && $ressource_id > 0) {
    $id = $ressource_id;
} else if ($request->ACT == "all") {
    $id = - 1;
} else if ($id_ressource > 0) {
    $id = $id_ressource;
} else {
    $id = 0;
}
?>
<div class="modal fade" id="videoModal" tabindex="-1" role="dialog" style="height: 103%;" aria-labelledby="videoModal" aria-hidden="true" >
    <div class="body-newframe">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="display: none;">&times;</button>
        <iframe id="frame_modal" width="100%" height="100%" style="padding: 0; margin: 0; border: 0;" src="" onLoad='var id_ressource = <?php echo json_encode($id); ?>;
                if (window.jQuery) {
                    $("#frame_modal").contents().find("a").click(function(event) {
                        var current = event.currentTarget;
                        if (current.className.search("clementine_crud-backbutton") == -1 && current.className.search("clementine_crud-update-blocked") == -1) {
                            event.preventDefault();
                        }
                    });
                    var current = this.contentWindow.location.href;
                    var update = 0;
                    if (current.search("reservation/update")) {
                        update = 1;
                    }
                    var url =  $(this).attr("src");
                    if (current != url && current.search("choix") == -1 && current.search("block") == -1 && current.search("delete") == -1 && update == 0) {
                        $(".close").trigger("click");
                        if (id_ressource == 0) {
                            $("#calendar1").fullCalendar("refetchEvents");
                        } else if (id_ressource != -1) {
                            $("#calendar"+id_ressource).fullCalendar("refetchEvents");
                        } else {
                            $("#calendar").fullCalendar("refetchEvents");
                        }
                    }
                }'></iframe>
    </div>
</div>
