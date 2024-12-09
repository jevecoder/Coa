<?php
// Query para kunin lahat ng login logs
$query = "SELECT login_logs.*, users.name AS user_name FROM login_logs 
          JOIN users ON login_logs.user_id = users.id 
          ORDER BY login_logs.timestamp DESC";
$result = $conn->query($query);
?>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form method="POST" action="index.php" id="loginForm">
    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <div class="d-flex justify-content-between">
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>

        <div>
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </div>
    </div>
    <button type="submit" class="btn btn-primary w-100" name="login">Login</button>
</form>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end login modal  -->


    <!-- directory modal  -->
    <div class="modal fade" id="directoryModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">TAG-H DIRECTORY</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table text-center table-hover">
                            <thead style="width:100%">
                                <tr>
                                    <th style="font-size: small;" scope="col"></th>
                                    <th style="font-size: small;" scope="col">Name</th>
                                    <th style="font-size: small;" scope="col">Designation</th>
                                    <th style="font-size: small;" scope="col">Section</th>
                                    <th style="font-size: small;" scope="col">Number</th>
                                    <th style="font-size: small;" scope="col">Email</th>
                                    <th style="font-size: small;" scope="col">Birthday</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider text-center">
                                <tr>
                                    <td style="font-size: 10px;">RLM</td>
                                    <td style="font-size: 10px;">ROLDAN L. MENCIANO</td>
                                    <td style="font-size: 10px;">STAS IV</td>
                                    <td style="font-size: 10px;"></td>
                                    <td style="font-size: 10px;">0917-844-4818</td>
                                    <td style="font-size: 10px;">"dremcam@yahoo.com
                                        dremcam860@gmail.com"</td>
                                    <td style="font-size: 10px;">April 18, 1960</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px;">ABB</td>
                                    <td style="font-size: 10px;">ARJIE B. BAGUYA</td>
                                    <td style="font-size: 10px;">STAS III</td>
                                    <td style="font-size: 10px;">Section B</td>
                                    <td style="font-size: 10px;">0927-620-2528</td>
                                    <td style="font-size: 10px;">baguya_arjie@yahoo.com</td>
                                    <td style="font-size: 10px;">March 6, 1974</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px;">BAAS</td>
                                    <td style="font-size: 10px;">BENN-ANTHONY A. SORIANO</td>
                                    <td style="font-size: 10px;">STAS III</td>
                                    <td style="font-size: 10px;">Section A</td>
                                    <td style="font-size: 10px;">0927-209-2587</td>
                                    <td style="font-size: 10px;">baasoriano@yahoo.com.ph</td>
                                    <td style="font-size: 10px;">July 28, 1986</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end directory modal  -->





    <!-- encoding  -->
    <div class="modal fade" id="encodingModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">ENCODING GUIDE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table text-center table-hover">
                            <thead>
                                <tr>
                                    <th style="font-size: small;" scope="col" rowspan="2">Process No.</th>
                                    <th style="font-size: small;" scope="col" rowspan="2">Activity</th>
                                    <th style="font-size: small;" scope="col" rowspan="2">STATUS/OUTPUT <br> UNDER EACH ACTIVITY</th>
                                    <th style="font-size: small;" scope="col" colspan="2">DURATION <br> (subject to change based on complexity of the case)</th>
                                    <th style="font-size: small;" scope="col" rowspan="2">Weighted Percentage</th>
                                </tr>
                                <tr>
                                    <th style="font-size: small;" scope="col">Fastest</th>
                                    <th style="font-size: small;" scope="col">Latest</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider text-center">
                                <tr>
                                    <td style="font-size: 10px;">1</td>
                                    <td style="font-size: 10px;">PRELIMINARY REVIEW</td>
                                    <td style="font-size: 10px;">EDSE, Facts of the Case, Documentary Requirement, Coordination Meeting w/ Agency's ATL</td>
                                    <td style="font-size: 10px;">5 Days</td>
                                    <td style="font-size: 10px;">10 Days</td>
                                    <td style="font-size: 10px;">10.00%</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px;">ABB</td>
                                    <td style="font-size: 10px;">ARJIE B. BAGUYA</td>
                                    <td style="font-size: 10px;">STAS III</td>
                                    <td style="font-size: 10px;">Section B</td>
                                    <td style="font-size: 10px;">0927-620-2528</td>
                                    <td style="font-size: 10px;">baguya_arjie@yahoo.com</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px;">BAAS</td>
                                    <td style="font-size: 10px;">BENN-ANTHONY A. SORIANO</td>
                                    <td style="font-size: 10px;">STAS III</td>
                                    <td style="font-size: 10px;">Section A</td>
                                    <td style="font-size: 10px;">0927-209-2587</td>
                                    <td style="font-size: 10px;">baasoriano@yahoo.com.ph</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end encoding  -->


    <!-- login_logs -->
    <div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                    <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are logs in the database
                if ($result->num_rows > 0) {
                    // Loop through each log and display it
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['user_name'] . "</td>";
                        echo "<td>" . $row['action'] . "</td>";
                        echo "<td>" . $row['ip_address'] . "</td>";
                        echo "<td>" . $row['timestamp'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No login logs found</td></tr>";
                }
                ?>
            </tbody>
        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
     <!-- login_logs end  -->