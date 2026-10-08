<?php
session_start();
$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();
require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/validate.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>customers</title> 
    <link rel="stylesheet" href="./CSS/cdetailtemplate.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    

    <div class="main" id="main">
        <h1>Employee Details</h1>
        <div class="container-main">
            <div class="left">
                

                    <?php
                
                        $idRaw = sanitize_input($_GET['id'] ?? '');
                        $id = ctype_digit($idRaw) ? (int) $idRaw : 0;

                        require_once 'config.php';
                        $conn = getDBConnection();

                  
                        $sql = "SELECT * FROM users WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        
                   
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $employeeId = (int) $row['id'];
                            $employeeName = htmlspecialchars($row['name']);
                            $employeeEmail = htmlspecialchars($row['email']);
                            $employeePhone = htmlspecialchars($row['phone']);
                            $employeePosition = htmlspecialchars($row['position']);
                            $employeeIdDisplay = sprintf('#%05d', $employeeId);
                        ?>

                        <div class="item">
                            <h3>Name:</h3>
                            <p><?php echo $employeeName; ?></p>
                        </div>
                        <div class="item">
                            <h3>ID: </h3>
                            <p><?php echo $employeeIdDisplay; ?></p>
                        </div>
                        <div class="item">
                            <h3>Email: </h3>
                            <p><?php echo $employeeEmail; ?></p>
                        </div>
                        <div class="item">
                            <h3>Phone: </h3>
                            <p><?php echo '+' . $employeePhone; ?></p>
                        </div>
                        <div class="item">
                            <h3>Position: </h3>
                            <p><?php echo $employeePosition; ?></p>
                        </div>

                        <?php if ($is_admin): ?>
                        <div class="actions">
                            <button type="button" class="remove-btn" id="open-remove-modal">Remove</button>
                        </div>

                        <div class="modal-backdrop" id="remove-modal" aria-hidden="true">
                            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="remove-modal-title">
                                <div class="modal-header">
                                    <h3 id="remove-modal-title">Warning</h3>
                                    <button type="button" class="modal-close" id="close-remove-modal" aria-label="Close">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to remove this employee?</p>
                                </div>
                                <div class="modal-footer">
                                    <form method="POST" action="set_employee.php" id="remove-employee-form">
                                        <input type="hidden" name="employee_id" value="<?php echo $employeeId; ?>">

                                        <button type="submit" name="remove_employee" class="modal-primary">Yes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php
                        } else {
                            echo "Employee not found.";
                        }


                    ?>

                

                
                
            </div>

            
            
        
            


        
        
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('remove-modal');
            const openBtn = document.getElementById('open-remove-modal');
            const closeBtn = document.getElementById('close-remove-modal');


            if (!modal || !openBtn) {
                return;
            }

            const toggleModal = (show) => {
                modal.classList.toggle('is-visible', show);
                modal.setAttribute('aria-hidden', show ? 'false' : 'true');
            };

            openBtn.addEventListener('click', () => toggleModal(true));
            [closeBtn].forEach((btn) => {
                if (btn) {
                    btn.addEventListener('click', () => toggleModal(false));
                }
            });
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    toggleModal(false);
                }
            });
        });
    </script>
</body>
</html>


                    
