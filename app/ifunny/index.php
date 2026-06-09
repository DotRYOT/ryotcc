<?php
$message = '';
$dataFile = 'data.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urls'])) {
    $urls = $_POST['urls'];
    $data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
    $addedCount = 0;

    foreach ($urls as $url) {
        $url = trim($url);
        if (empty($url)) continue;

        // Fetch the HTML content using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // Important: Set a User-Agent to avoid being blocked
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36');
        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html) continue;

        $mediaUrl = null;
        $type = 'image';

        // Extract video URL
        if (preg_match('/<meta property="og:video:url"\s+content="([^"]+)"/i', $html, $matches)) {
            $mediaUrl = html_entity_decode($matches[1]);
            $type = 'video';
        } 
        // Fallback: extract image URL if no video
        elseif (preg_match('/<meta property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            $mediaUrl = html_entity_decode($matches[1]);
            $type = 'image';
        }

        if ($mediaUrl) {
            // Prepend new media to the array
            array_unshift($data, [
                'id' => uniqid(),
                'url' => $mediaUrl,
                'type' => $type,
                'original' => $url,
                'added_at' => time(),
                'viewed' => false,
                'viewed_at' => 0
            ]);
            $addedCount++;
        }
    }

    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    
    if ($addedCount > 0) {
        $message = "<div class='alert alert-success'>Successfully scraped and added $addedCount media links!</div>";
    } else {
        $message = "<div class='alert alert-warning'>No valid media could be found on the provided links.</div>";
    }
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4">Upload iFunny Links</h2>
        <?= $message ?>
        <div class="card bg-dark text-light border-secondary">
            <div class="card-body">
                <form method="post" id="uploadForm">
                    <div id="urlInputs">
                        <div class="input-group mb-3">
                            <input type="url" name="urls[]" class="form-control bg-dark text-light border-secondary" placeholder="https://ifunny.co/video/..." required>
                            <button type="button" class="btn btn-outline-danger remove-btn" disabled>Remove</button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <button type="button" id="addMoreBtn" class="btn btn-success">+ Add Another Link</button>
                        <button type="submit" class="btn btn-primary">Scrape & Save Links</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function updateRemoveButtons() {
        if ($('.remove-btn').length > 1) {
            $('.remove-btn').prop('disabled', false);
        } else {
            $('.remove-btn').prop('disabled', true);
        }
    }

    $('#addMoreBtn').click(function() {
        $('#urlInputs').append(`
            <div class="input-group mb-3" style="display:none;">
                <input type="url" name="urls[]" class="form-control bg-dark text-light border-secondary" placeholder="https://ifunny.co/video/..." required>
                <button type="button" class="btn btn-outline-danger remove-btn">Remove</button>
            </div>
        `);
        $('#urlInputs .input-group:last').slideDown(200);
        updateRemoveButtons();
    });

    $(document).on('click', '.remove-btn', function() {
        if ($('.remove-btn').length > 1) {
            $(this).closest('.input-group').slideUp(200, function() {
                $(this).remove();
                updateRemoveButtons();
            });
        }
    });

    // Make pressing Enter add a new line and focus instead of submitting the form
    $(document).on('keypress', 'input[name="urls[]"]', function(e) {
        if (e.which === 13) {
            e.preventDefault(); // Prevent form submission
            $('#addMoreBtn').click(); // Add a new input field
            $('#urlInputs input[name="urls[]"]').last().focus(); // Focus on the new input field
        }
    });
});
</script>

<?php include 'footer.php'; ?>