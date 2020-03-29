<?php

require_once('settings.php');

require_once ('templates/header.php');
?>



<main role="main">
    <div style="min-height: 50px;"></div>
    <div style="text-align: center;">
        <div class="btn-group" role="group" aria-label="Category">
            <button type="button" class="btn btn-secondary" onclick="location.href = 'index.php';">All</button>
        <?php
        try {
            $dbh = new PDO(
                'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME
                .'',
                DB_USER,
                DB_PASSWORD
            );
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $error) {
            echo 'Connection failed with error: '.$error->getMessage();
        }

        $stmt = $dbh->query('SELECT DISTINCT category FROM targets WHERE category IS NOT NULL');
        $categories = $stmt->fetchAll();
        foreach ($categories as $category){
            echo ' <button type="button" class="btn btn-secondary" onclick="location.href = \'index.php?category='.urlencode($category['category']).'\';">'.$category['category'].'</button>';
        }
        ?>


    </div></div><br>
    <!-- Uptime boxes
    ================================================== -->
    <!-- Wrap the rest of the page in another container to center all the content. -->

    <div class="container uptime">

        <div class="row">

            <?php

            try {
                $dbh = new PDO(
                    'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME
                    .'',
                    DB_USER,
                    DB_PASSWORD
                );
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $error) {
                echo 'Connection failed with error: '.$error->getMessage();
            }

            if (!empty($_GET['category'])){
                $stmt = $dbh->prepare(
                    'SELECT * FROM targets WHERE category = :category'
                );
                $stmt->execute(['category' => urldecode($_GET['category'])]);
            }else{
                $stmt = $dbh->query('SELECT * FROM targets');
            }
            $targets = $stmt->fetchAll();
            foreach ($targets as $target) {
                echo '<div class="col-lg-4" style="border: 1px solid #80808024;padding-top: 10px;">
                <canvas id="uptimechart-target'.$target['id'].'"></canvas>
                <h2>'.$target['name'].'</h2>
                <hr>
            <p>
            
            <button type="button" class="btn btn-secondary" onclick="location.href = \'detailedGraph.php?id='.$target['id'].'\';">🔍&nbsp;Last 24 hrs</button>
              </p>
              <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#urlModal'
                    .$target['id'].'"">🌎&nbsp;URL</button>
              </p>
<!-- URL modal -->
<div class="modal fade" id="urlModal'.$target['id']
                    .'" tabindex="-1" role="dialog" aria-labelledby="urlModal'
                    .$target['id'].'Label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="urlModal'.$target['id'].'Label">URL of monitored service</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <a href="'.$target['url'].'">'.$target['url'].'</a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

                <p>';

                $stmt = $dbh->prepare(
                    'SELECT * FROM data WHERE target_id = :target_id ORDER BY datetime DESC LIMIT 1'
                );
                $stmt->execute(['target_id' => $target['id']]);
                $lastRow = $stmt->fetch();

                // last status check
                if ($lastRow !== false && $lastRow['status'] == 1) {
                    echo '<a class="btn btn-success"
                      role="button" disabled>🖥️&nbsp;STATUS:&nbsp;UP</a>';
                } else {
                    echo '<a class="btn btn-failure"
                      role="button" disabled>🖥️&nbsp;STATUS:&nbsp;DOWN</a>';
                }


                echo '
</p>
            </div><!-- /.col-lg-4 -->';
                $stmt = $dbh->prepare(
                    'SELECT * FROM data WHERE target_id = :target_id ORDER BY datetime DESC LIMIT 12'
                );
                $stmt->execute(['target_id' => $target['id']]);
                $targetData[$target['id']] = array_reverse($stmt->fetchAll());
            }

            $pdo = null;
            ?>
        </div><!-- /.row -->

    </div><!-- /.container -->

    <?php
    require_once('templates/footer.php');
    ?>

<?php
foreach ($targetData as $key => $data) {
    $labels = '';
    $values = '';
    foreach ($data as $check) {
        $formattedDate = date('d/m/y H:i', $check['datetime']);
        $labels .= ",'".$formattedDate."'";
        $values .= ','.$check['status'];
    }
    $valuesWithLabels = str_replace(
        '1',
        '"UP"',
        str_replace('0', '"DOWN"', $values)
    );
    echo "<script>var ctx = document.getElementById('uptimechart-target".$key."').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',

        data: {
            labels: [".$labels."],
            datasets: [{
                label: 'status',
                backgroundColor: 'rgb(34,146,255)',
                borderColor: 'rgb(34,146,255)',
                data: [".$valuesWithLabels."]
            }]
        },

        // Configuration options go here
        options: {
            legend: {
                display: false
            },
            scales: {
            yAxes: [{
                type: 'category',
                labels: ['UP', 'DOWN'],
                 ticks: {
                    min: 'UP'
                }
            }]
        }
        }
    });</script>";
}
?>
</body>
</html>

