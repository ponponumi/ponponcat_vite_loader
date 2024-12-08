<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>test</title>
    <?php wp_head() ?>
</head>
<body>
    <h1><?php the_title() ?></h1>
    <div><?php the_content() ?></div>
    <?php wp_footer() ?>
</body>
</html>
