<?php include_once("header.php"); ?>

<div class="container">
    <!-- Create auction form -->
    <div style="max-width: 800px; margin: 10px auto">
        <h2 class="new-auction-header">Create New Auction</h2>
        <div class="card">
            <div class="card-body">
                <!-- Note: This form does not do any dynamic/client-side/JavaScript-based validation of data. It only performs checking after the form has been submitted, and only allows users to try once. You can make this fancier using JavaScript to alert users of invalid data before they try to send it, but that kind of functionality should be extremely low-priority/only done after all database functions are complete. -->
                <form method="post" action="create_auction_result.php">
                    <div class="form-group row">
                        <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of auction</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="auctionTitle" name="productName" placeholder="e.g. Diamond Ring">
                            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            <small id="detailsHelp" class="form-text text-muted">Full details of the listing to help bidders decide if it's what they're looking for.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Category</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Type of Jewelry</strong>
                                    <select class="form-control" name="CategoryName[]" multiple>
                                        <option value="Watches">Watches</option>
                                        <option value="Rings">Rings</option>
                                        <option value="Bracelets">Bracelets</option>
                                        <option value="Earrings">Earrings</option>
                                        <option value="Necklaces">Necklaces</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <strong>Gender of Jewelry</strong>
                                    <select class="form-control" name="CategoryName[]" multiple>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Unisex">Unisex</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <strong>Material of Jewelry</strong>
                                    <select class="form-control" name="CategoryName[]" multiple>
                                        <option value="Gold">Gold</option>
                                        <option value="Silver">Silver</option>
                                        <option value="Bronze">Bronze</option>
                                        <option value="Platinum">Platinum</option>
                                        <option value="Rose Gold">Rose Gold</option>
                                        <option value="Gemstone">Gemstone</option>
                                    </select>
                                </div>
                            </div>
                            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select one or more categories for this item.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">£</span>
                                </div>
                                <input type="number" class="form-control" id="startPrice" name="startPrice">
                            </div>
                            <small id="startBidHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Initial bid amount.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">£</span>
                                </div>
                                <input type="number" class="form-control" id="reservePrice" name="reservePrice">
                            </div>
                            <small id="reservePriceHelp" class="form-text text-muted">Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
                        <div class="col-sm-10">
                            <input type="datetime-local" class="form-control" id="endDate" name="endDate">
                            <small id="endDateHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Day for the auction to end.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionCondition" class="col-sm-2 col-form-label text-right">Product Condition</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="auctionCondition" name="productCondition">
                                <option selected>Choose...</option>
                                <option value="new">New</option>
                                <option value="used">Used</option>
                            </select>
                            <small id="conditionHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select the condition of the item.</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary form-control">Create Auction</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once("footer.php"); ?>