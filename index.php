<?php
//Config
$dir = "/home/yannick/Downloads/today";
$showDir = false;

//Lib
function error($code = 404, $message = "Not found"){
    echo "<h1>$code $message</h1>";
    die;
}
function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

//Routing
if(!is_dir($dir)){
    error(500,"Invalid configuration");
}
if(isset($_GET['download'])){
    $file = $_GET['download'];
    if(!$showDir){
        $file = $dir.DIRECTORY_SEPARATOR.$file;
    }
    //Check if file is in opened dir


    //Check if file is a file
    if(!is_file($file)){
        error(404);
    }

    //Download file
    $finfo = finfo_open(FILEINFO_MIME);
    $mimetype = finfo_file($finfo,$file);
    header('Content-Type: '.$mimetype);
    header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
    readfile($file);
} else {
    //Controller
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
        RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
    );


    $paths = array();
    foreach ($iter as $path => $fileDir) {
        if(is_dir($path)){
            $paths[$path] = array();
        } else {
            $pathinfo = pathinfo($path);
            $pathinfo['size'] = filesize($path);
            $pathinfo['modified_at'] = date('Y-m-d H:i:s',filemtime($path));
            $pathinfo['created_at'] = date('Y-m-d H:i:s',filectime($path));
            if($showDir){
                $pathinfo['download'] = $pathinfo['dirname'].DIRECTORY_SEPARATOR.$pathinfo['basename'];
            } else {
                $pathinfo['download'] = $pathinfo['basename'];
            }

            $paths[$pathinfo['dirname']][] = $pathinfo;
        }
    }
    ksort($paths);
}



//View 

?>
<html>
	<head>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
        <link href="//vjs.zencdn.net/4.10/video-js.css" rel="stylesheet">

        <!-- It's often recommended now to put JavaScript before the end body tag (</body>) instead of the head (<head>),
        but Video.js includes an 'HTML5 Shiv', which needs to be in the head for older IE versions to respect the
        video tag as a valid element. -->
        <script src="//vjs.zencdn.net/4.10/video.js"></script>
        <title>
            <?php if($showDir): ?>
                <?php echo $dir; ?>
            <?php else: ?>
                Directory contents
            <?php endif; ?>
        </title>
    </head>
	<body>
        <div class="container">
            <h1 class="page-header">
                <?php if($showDir): ?>
                    <?php echo $dir; ?>
                <?php else: ?>
                    Directory contents
                <?php endif; ?>
            </h1>
            <form class="form-inline" role="form">
                <div class="form-group">
                    <div class="input-group">
                        <label class="sr-only" for="filter">Filter</label>
                        <div class="input-group-addon">Filter</div>
                        <input type="email" class="form-control" id="filter" placeholder="...">
                    </div>
                </div>
            </form>
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>Dir</th>
                    <th>Filename</th>
                    <th>Size</th>
                    <th>Modified at</th>
                    <th>Created at</th>
                    <th>Extension</th>
                </tr>
                </thead>
                <tbody class="searchable">
                <?php foreach($paths as $fileDir => $files): ?>
                        <?php foreach($files as $file): ?>
                            <tr class="searchable">
                                <td>
                                    <a data-toggle="tooltip"
                                       title="Download"
                                       href="index.php?download=<?php echo $file['download']; ?>">
                                        <span class="glyphicon glyphicon-download-alt"></span>
                                    </a>
                                    <?php if(in_array($file['extension'], array('jpeg','jpg','gif','png'))): ?>
                                        <a class="view-image"
                                           data-toggle="tooltip"
                                           title="View"
                                           filename="<?php echo $file['basename']; ?>"
                                           href="index.php?download=<?php echo $file['download']; ?>">
                                            <span class="glyphicon glyphicon-picture"></span>
                                        </a>
                                    <?php endif ?>
                                    <?php if(in_array($file['extension'], array('mp4','ogv'))): ?>
                                        <a class="view-video"
                                           data-toggle="tooltip"
                                           title="View"
                                           filename="<?php echo $file['basename']; ?>"
                                           href="index.php?download=<?php echo $file['download']; ?>">
                                            <span class="glyphicon glyphicon-facetime-video"></span>
                                        </a>
                                    <?php endif ?>
                                </td>
                                <td style="max-width: 300px">
                                    <?php if($showDir): ?>
                                        <?php echo $dir; ?>
                                    <?php else: ?>
                                        <?php echo str_replace($dir,DIRECTORY_SEPARATOR,str_replace($dir.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,$fileDir)); ?>
                                    <?php endif; ?>
                                </td>
                                <td style="width: 300px"><?php echo $file['basename']; ?></td>
                                <td>
                                    <?php echo human_filesize($file['size']); ?>
                                </td>
                                <td><?php echo $file['modified_at']; ?></td>
                                <td><?php echo $file['created_at']; ?></td>
                                <td><?php echo $file['extension']; ?></td>


                            </tr>
                        <?php endforeach; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="fileview-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title">Modal title</h4>
                    </div>
                    <div class="modal-body">
                        <p>One fine body&hellip;</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <script>
            $(function () {
                $('[data-toggle="popover"]').popover();
                $('[data-toggle="tooltip"]').tooltip();

                $('#filter').keyup(function () {
                    var rex = new RegExp($(this).val(), 'i');
                    $('.searchable tr').hide();
                    $('.searchable tr').filter(function () {
                        return rex.test($(this).text());
                    }).show();
                });

                $('.view-image').click(function(event){
                    event.preventDefault();
                    var url = $(this).attr('href');
                    var filename = $(this).attr('filename');
                    var html = '<img src="'+url+'" />';
                    var title = "View: "+filename;
                    $('#fileview-modal h4.modal-title').html(title);
                    $('#fileview-modal div.modal-body').html(html);
                    $('#fileview-modal').modal();
                });

                $('.view-video').click(function(event){
                   event.preventDefault();
                    var url = $(this).attr('href');
                    var filename = $(this).attr('filename');
                    var html = '<video id="videoPlayer" class="video-js vjs-default-skin"'+
                    'controls preload="auto" width="768" height="480">'+
                    '<source src="'+url+'" type="video/mp4" />'+
                    '<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>'+
                    '</video>';
                    var title = "View: "+filename;
                    $('#fileview-modal h4.modal-title').html(title);
                    $('#fileview-modal div.modal-body').html(html);
                    videojs("videoPlayer", {}, function(){
                        // Player (this) is initialized and ready.
                    });
                    $('#fileview-modal').on('hidden.bs.modal',function(){
                        $('video').remove();
                    })
                    $('#fileview-modal').modal();
                });
            })
        </script>
	</body>
</html>
