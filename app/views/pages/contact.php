<?php $feedbackEntries = $feedbackEntries ?? []; ?>
<section class="mb-3">
    <h1>Contact and Feedback</h1>
    <p class="text-muted">Send feedback about your shopping experience and suggestions for better Gikomba deals.</p>
</section>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="surface-card p-4 mb-3">
            <h5>Feedback Form</h5>
            <form class="row g-3" method="post" action="index.php?page=contact" id="feedbackForm" data-validate="feedback">
                <?= csrf_field() ?>
                <div class="col-md-6"><label class="form-label">Full Name</label><input class="form-control" name="name" placeholder="Your Name" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" placeholder="Your Email" required></div>
                <div class="col-md-6">
                    <label class="form-label">Rating</label>
                    <select class="form-select" name="rating" required>
                        <option value="">Select rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Feedback Message</label><textarea class="form-control" name="message" rows="4" placeholder="Tell us your experience" required></textarea></div>
                <div class="col-12"><button class="btn btn-accent" type="submit">Submit Feedback</button></div>
            </form>
        </div>
        <div class="surface-card p-4">
            <h5>Customer Feedback (From MySQL)</h5>
            <?php if (!$feedbackEntries): ?>
                <p class="mb-0 text-muted">No feedback submitted yet.</p>
            <?php else: ?>
                <?php foreach ($feedbackEntries as $entry): ?>
                    <div class="border-bottom pb-2 mb-2">
                        <p class="mb-1"><strong><?= e((string) $entry['name']) ?></strong> <span class="text-muted">(<?= e((string) $entry['email']) ?>)</span></p>
                        <p class="mb-1">Rating: <?= str_repeat('★', (int) $entry['rating']) ?></p>
                        <p class="mb-0 text-muted"><?= e((string) $entry['message']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="surface-card p-4 mb-3">
            <h5>Contact Details</h5>
            <p class="mb-1"><strong>Phone:</strong> +254 700 123 456</p>
            <p class="mb-1"><strong>Phone 2:</strong> +254 711 987 654</p>
            <p class="mb-1"><strong>Email:</strong> support@gikombastore.test</p>
            <p class="mb-0"><strong>Hours:</strong> Mon-Sat, 8:00 AM - 7:00 PM</p>
        </div>
        <div class="surface-card p-4">
            <h5>Feedback Tips</h5>
            <p class="mb-2">Write what item you bought, quality, and delivery speed.</p>
            <p class="mb-0">Your feedback helps improve affordable second-hand shopping for other students.</p>
        </div>
    </div>
</div>
