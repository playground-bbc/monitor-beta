<div class="row">
    <div class="col-md-12">
        <!-- show  terms searched -->
        <h2 style="font-family: 'Helvetica', sans-serif;"><?= $model->name ?></h2>
        <h1 style="font-family: 'Helvetica', sans-serif;">Escucha</h1>

        <?php foreach($model->products as $term): ?>
            <p><?= $term ?></p>
        <?php endforeach; ?>  
        <!-- end show  terms searched -->
    </div>
</div>