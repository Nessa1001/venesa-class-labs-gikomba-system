    </div>
</main>
<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <h5>Gikomba Marketplace</h5>
                <p>Affordable second-hand fashion for campus life and everyday wear. Good quality, friendly prices, and simple ordering.</p>
            </div>
            <div class="col-md-2">
                <h6>Shop</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="index.php?page=categories">Categories</a></li>
                    <li><a href="index.php?page=shop">Shop</a></li>
                    <li><a href="index.php?page=cart">Cart</a></li>
                    <li><a href="index.php?page=wishlist">Wishlist</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6>Company</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="index.php?page=about">About Us</a></li>
                    <li><a href="index.php?page=contact">Contact Us</a></li>
                    <li><a href="index.php?page=tracking">Track Order</a></li>
                    <li><a href="products.php">Products Demo</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Newsletter</h6>
                <form class="newsletter-form" method="post" action="index.php?page=subscribe">
                    <?= csrf_field() ?>
                    <div class="input-group">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        <button class="btn btn-accent" type="submit">Subscribe</button>
                    </div>
                </form>
                <small class="d-block mt-3">Business Hours: Mon-Sat, 8:00 AM - 7:00 PM</small>
            </div>
        </div>
        <hr class="my-4">
        <p class="mb-0 text-center">&copy; <?= date('Y') ?> Gikomba Store. All rights reserved.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="public/assets/js/app.js"></script>
</body>
</html>
