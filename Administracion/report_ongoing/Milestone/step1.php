<?php include '../header.php'; ?>

    <form action="step2.php" method="post" role="form">
        <fieldset>
            <legend>Specify your Assembla credentials</legend>

            <div class="row">
                <div class="form-group col-md-4">
                    <label for="key">Assembla Key</label>
                    <input type="text" id="key" name="key" class="form-control" value="cfbc848c5e2816ac3381" placeholder="Assembla Key" required="required">
                </div>

                <div class="form-group col-md-4">
                    <label for="secret">Assembla Secret</label>
                    <input type="text" id="secret" name="secret" class="form-control" value="8f21a57b9b86dedeafdc22f34d90d5dff93130f4" placeholder="Assembla Secret" required="required">
                </div>

                <div class="form-group col-md-4">
                    <label for="project">Assembla Project</label>
                    <input type="text" id="project" name="project" class="form-control" value="" placeholder="Assembla Project" required="required">
                </div>
            </div>


        </fieldset>

        <button type="submit" class="btn btn-lg btn-primary">Next</button>
    </form>

<?php include '../footer.php'; ?>