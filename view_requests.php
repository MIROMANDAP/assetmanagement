<?php date_default_timezone_set('Asia/Shanghai'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Same CSS as before, with a few modifications for AJAX */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.3s;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-primary {
            background-color: #007bff;
            text-align: center;
            display: inline-block;
            margin: 20px auto;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pending Issued To Change Requests</h1>
        <table id="requestsTable">
            <thead>
                <tr>
                    <th>Asset Tag</th>
                    <th>Old Issued To</th>
                    <th>New Issued To</th>
                    <th>Reason</th>
                    <th>Requested By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dynamic content will be loaded here -->
            </tbody>
        </table>
        <div style="text-align: center;">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div </div>
    </div>
   <script>
    function fetchRequests() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_requests.php', true);
        xhr.onload = function() {
            if (this.status === 200) {
                const response = JSON.parse(this.responseText);
                const tbody = document.querySelector('#requestsTable tbody');
                tbody.innerHTML = ''; // Clear existing rows

                if (response.requests && response.requests.length > 0) {
                    response.requests.forEach(request => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td data-label="Asset Tag">${request.asset_tag}</td>
                            <td data-label="Old Issued To">${request.old_issued_to}</td>
                            <td data-label="New Issued To">${request.new_issued_to}</td>
                            <td data-label="Reason">${request.reason}</td>
                            <td data-label="Requested By">${request.firstname} ${request.lastname}</td>
                            <td class="action-buttons">
                                <form action="approve_request.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="${request.request_id}">
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </form>
                                <form action="reject_request.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="${request.request_id}">
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </form>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No pending requests</td></tr>';
                }
            } else {
                console.error('Failed to fetch requests:', this.status, this.statusText);
            }
        };
        xhr.onerror = function() {
            console.error('Request failed');
        };
        xhr.send();
    }

    // Fetch requests every 5 seconds
    setInterval(fetchRequests, 5000);
    // Initial fetch
    fetchRequests();
</script>
</body>
</html>