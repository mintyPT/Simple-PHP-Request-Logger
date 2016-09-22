<?php

    include_once 'config.php';

    $params = array();


    $limit = 50;
    if (isset($_GET['l'])) {
        $limit = mysqli_real_escape_string($con, $_GET['l']);
        $params['l'] = $limit;
    }

    $page = 1;
    if (isset($_GET['p'])) {
        $page = (int)mysqli_real_escape_string($con, $_GET['p']);
        $params['p'] = $page;
    }

    $order = 'id';
    if (isset($_GET['o'])) {
        $order = mysqli_real_escape_string($con, $_GET['o']);
        $params['o'] = $order;
    }

    $orderDirection = 'DESC';
    if (isset($_GET['od'])) {
        $orderDirection = mysqli_real_escape_string($con, $_GET['od']);
        $params['od'] = $orderDirection;
    }


    $apiKey = !empty($_GET['key']) ? $_GET['key'] : '';
    if ($apiKey != 'snowman') {
        die('> invalid api key');
    }
    $params['key'] = $apiKey;


?>


<html>
<head>

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css"
          href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">

    <!-- jQuery -->
    <script type="text/javascript" charset="utf8"
            src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>

    <!-- DataTables -->
    <script type="text/javascript" charset="utf8"
            src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.1/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.1/css/bootstrap-theme.min.css">

    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>

    <script type="text/javascript">

        $(document).ready(function () {
            $('#example').dataTable({
                "bSort"    : false,
                //"aaSorting" : [[]],
                "bPaginate": false
            });
            fnShowHide(7);
            fnShowHide(8);
            fnShowHide(9);
            fnShowHide(10);
        });

        function fnShowHide(iCol) {
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var oTable = $('#example').dataTable();

            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, bVis ? false : true);
        }

    </script>

</head>
<body>


<div class="container">

    <div class="row">
        <div class="col-xs-12 col-sm-4">
            <h1>Requests</h1>
        </div>
        <div class="col-xs-12 col-sm-8 text-right">
            <br>
            <br>
            <b>Hide columns:</b>
            <a href="javascript:void(0);" onclick="fnShowHide(0);">id</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(1);">datetime</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(2);">ip</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(3);">hostname</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(4);">uri</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(5);">agent</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(6);">referer</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(7);">domain</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(8);">filename</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(9);">method</a> |
            <a href="javascript:void(0);" onclick="fnShowHide(10);">data</a>
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col-xs-12">
            <table class="table table-striped table-bordered table-condensed table-hover" id="example">
                <thead>
                <tr>
                    <th>id</th>
                    <th>datetime</th>
                    <th>ip</th>
                    <th>hostname</th>
                    <th>uri</th>
                    <th>agent</th>
                    <th>referer</th>
                    <th>domain</th>
                    <th>filename</th>
                    <th>method</th>
                    <th>data</th>
                </tr>
                </thead>
                <tbody>

                <?php


                    $offset = ((int)$page - 1) * $limit;

                    $sql = sprintf("SELECT * FROM hits ORDER BY %s %s LIMIT %s, %s", $order, $orderDirection, $offset, $limit);


                    $sql2 = sprintf("SELECT count(*) FROM hits", $order, $orderDirection, $offset, $limit);
                    $result2 = mysqli_query($con, $sql2);
                    $rows = mysqli_fetch_row($result2);
                    $total = $rows[0];
                    $total_pages = ceil($total / $limit);


                    $result = mysqli_query($con, $sql);

                    $data = array();
                    while ($row = mysqli_fetch_array($result))
                        $data[] = $row;


                    foreach ($data as $row) {
                        echo "\n";
                        echo "<tr>\n";
                        echo "<td>" . $row['id'] . "</td>\n";
                        echo "<td>" . $row['datetime'] . "</td>\n";
                        echo "<td>" . make_whois($row['ip']) . make_loc($row['ip']) . "</td>\n";
                        echo "<td>" . br2(gethname($row['ip'])) . "</td>\n";
                        echo "<td>" . br2($row['uri']) . "</td>\n";
                        echo "<td>" . br1($row['agent']) . "</td>\n";
                        echo "<td>" . br2($row['referer'], 30) . "</td>\n";
                        echo "<td>" . $row['domain'] . "</td>\n";
                        echo "<td>" . $row['filename'] . "</td>\n";
                        echo "<td>" . $row['method'] . "</td>\n";
                        echo "<td>" . $row['data'] . "</td>\n";
                        echo "</tr>\n";
                    }

                    mysqli_close($con);
                ?>

                </tbody>
            </table>

        </div>
    </div>

    <?php if ($total_pages > 1): ?>


        <div class="row">
            <div class="col-xs-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm">

                        <?php if ($page > 1):

                            $params['p'] = $page - 1;
                            $strParams = http_build_query($params);
                            $url = '/?' . $strParams;

                            ?>

                            <li>
                                <a href="<?php echo $url ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>

                        <?php endif; ?>

                        <?php

                            for ($i = 0; $i < $total_pages; $i++):

                                $params['p'] = $i + 1;
                                $strParams = http_build_query($params);
                                $url = '/?' . $strParams;

                                $class = '';
                                if ($i + 1 == $page) {
                                    $class = 'active';
                                }
                                ?>
                                <li class="<?php echo $class ?>"><a href="<?php echo $url ?>"><?php echo $i + 1; ?></a>
                                </li>
                                <?php
                            endfor;
                        ?>


                        <?php if ($page < $total_pages):
                            $params['p'] = $page + 1;
                            $strParams = http_build_query($params);
                            $url = '/?' . $strParams;

                            ?>
                            <li>
                                <a href="<?php echo $url; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>


    <?php endif; ?>


    <br><br><br>

    <br><br>
</div>


</body>
</html>

