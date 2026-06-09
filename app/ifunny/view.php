<?php
$dataFile = 'data.json';

// Handle POST actions for delete and mark_viewed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_all') {
        file_put_contents($dataFile, json_encode([]));
        header('Location: view.php');
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'mark_viewed') {
        $id = $_POST['id'] ?? '';
        $data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
        foreach ($data as &$item) {
            $itemId = isset($item['id']) ? $item['id'] : md5($item['url']);
            if ($itemId === $id) {
                $item['viewed'] = true;
                $item['viewed_at'] = time();
                break;
            }
        }
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
        exit('OK');
    }
}
?>

<?php include 'header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col position-relative">
        <h2 class="text-center mb-0">Media Gallery</h2>
        <form method="post" class="position-absolute top-50 translate-middle-y" style="right: 15px;" onsubmit="return confirm('Are you sure you want to delete ALL posts? This cannot be undone.');">
            <input type="hidden" name="action" value="delete_all">
            <button type="submit" class="btn btn-danger btn-sm">Delete All Posts</button>
        </form>
    </div>
</div>

<?php
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

$lastViewedId = null;
$maxViewedAt = 0;
foreach ($data as $item) {
    if (!empty($item['viewed_at']) && $item['viewed_at'] > $maxViewedAt) {
        $maxViewedAt = $item['viewed_at'];
        $lastViewedId = isset($item['id']) ? $item['id'] : md5($item['url']);
    }
}

if (empty($data)):
?>
    <div class="alert alert-info text-center">
        No media found. <a href="index.php" class="alert-link">Upload some iFunny links here</a>.
    </div>
<?php else: ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex flex-column gap-5 mb-5 pb-5">
                <?php foreach ($data as $index => $item): 
                    $itemId = isset($item['id']) ? $item['id'] : md5($item['url']);
                    $isViewed = !empty($item['viewed']);
                ?>
                    <div class="card bg-dark text-light border-secondary shadow-lg media-item <?= $isViewed ? 'border-success' : '' ?>" id="item-<?= $itemId ?>" data-id="<?= $itemId ?>">
                        <div class="card-header border-secondary d-flex justify-content-between">
                            <span class="viewed-badge badge <?= $isViewed ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $isViewed ? 'Viewed' : 'Not Viewed' ?>
                            </span>
                            <small class="text-muted">Added: <?= date('M j, Y g:i A', $item['added_at'] ?? time()) ?></small>
                        </div>
                        <div class="card-body p-0 text-center bg-black">
                            
                            <?php if ($item['type'] === 'video'): ?>
                                <video controls class="d-block w-100 mx-auto track-view" style="max-height: 80vh; object-fit: contain;">
                                    <source src="<?= htmlspecialchars($item['url']) ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($item['url']) ?>" class="d-block w-100 mx-auto track-view" style="max-height: 80vh; object-fit: contain; cursor: pointer;" alt="iFunny Image" title="Click to mark as viewed">
                            <?php endif; ?>
                            
                        </div>
                        <div class="card-footer text-center border-secondary py-3">
                            <a href="<?= htmlspecialchars($item['original']) ?>" target="_blank" class="text-white text-decoration-none fw-bold">
                                View Original IFunny Post ↗
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    <?php if ($lastViewedId): ?>
    let lastViewed = document.getElementById('item-<?= $lastViewedId ?>');
    if (lastViewed) {
        lastViewed.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    <?php endif; ?>

    function markViewed(element) {
        let card = $(element).closest('.media-item');
        let id = card.data('id');
        let badge = card.find('.viewed-badge');
        
        if (!card.hasClass('border-success')) {
            card.addClass('border-success');
            badge.removeClass('bg-secondary').addClass('bg-success').text('Viewed');
            
            $.post('view.php', { action: 'mark_viewed', id: id });
        }
    }

    $('video.track-view').on('play', function() {
        markViewed(this);
    });

    $('img.track-view').on('click', function() {
        markViewed(this);
    });
});
</script>

<?php include 'footer.php'; ?>