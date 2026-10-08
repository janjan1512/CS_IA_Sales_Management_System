<?php
declare(strict_types=1);

if (!function_exists('load_topnav')) {
    function load_topnav(): void
    {
        ?>
        <div class="topnav">
            <svg id="side-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <div class="account">
                <a href="./profile.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </a>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('load_sidebar')) {
    function load_sidebar(bool $is_admin): void
    {
        ?>
        <div class="side-bar" id="side-bar">
            <ul class="main-dir">
                <div class="container-nav">
                    <li>
                        <a href="#">Orders</a>
                    </li>
                    <svg class="down" id="down-icon1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                    <svg class="up" id="up-icon1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                    </svg>
                </div>
                <ul class="sub-dir" id="sub-dir1">
                    <li>
                        <a href="./orders-pending.php">Pending</a>
                    </li>
                    <li>
                        <a href="./orders-inprogress.php">In Progress</a>
                    </li>
                    <li>
                        <a href="./orders-completed.php">Completed</a>
                    </li>
                </ul>
                <li>
                    <a href="./customers.php">Customers</a>
                </li>
                <div class="container-nav">
                    <li>
                        <a href="#">Sales</a>
                    </li>
                    <svg class="down" id="down-icon2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                    <svg class="up" id="up-icon2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                    </svg>
                </div>
                <ul class="sub-dir" id="sub-dir2">
                    <li>
                        <a href="./sales-overall-table.php">Overall</a>
                    </li>
                    <li>
                        <a href="./sales-customers-table.php">Per Customer</a>
                    </li>
                    <li>
                        <a href="./sales-forecast.php">Forecast</a>
                    </li>
                </ul>
                <?php if ($is_admin): ?>
                    <div class="admin-only" id="admin-only">
                        <li>
                            <a href="./employees.php">Employees</a>
                        </li>
                        <div class="container-nav">
                            <li>
                                <a href="#">Requests</a>
                            </li>
                            <svg class="down" id="down-icon3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                            <svg class="up" id="up-icon3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                            </svg>
                        </div>
                        <ul class="sub-dir" id="sub-dir3">
                            <li>
                                <a href="./pending.php">Pending</a>
                            </li>
                            <li>
                                <a href="./resolved.php">Resolved</a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </ul>
        </div>
        <?php
    }
}

