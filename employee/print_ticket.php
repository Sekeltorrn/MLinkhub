<?php
// employee/print_ticket.php
require_once '../db.php';
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

// 2. Get Ticket ID
if (!isset($_GET['ticket_no'])) {
    die("Error: No ticket number specified.");
}

$ticket_no = $_GET['ticket_no'];
$branch_id = $_SESSION['branch_id'];

// 3. Fetch Full Data
$sql = "SELECT l.*, c.first_name, c.last_name, c.middle_name, c.address, c.contact_number, c.valid_id_type, c.valid_id_num 
        FROM loans l
        JOIN customers c ON l.customer_id = c.user_id
        WHERE l.pawn_ticket_number = ? AND l.branch_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $ticket_no, $branch_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Ticket not found.");
}

$row = $result->fetch_assoc();

// 4. Calculations
$appraised_value = $row['principal_amount'] / 0.60;
$pawner_name = strtoupper($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Ticket - <?= $ticket_no ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Base styles */
        body {
            background-color: #f3f4f6; /* Light gray background for screen view */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            min-height: 100vh;
        }

        /* The Physical Ticket Size (Approx 8.5 x 5.5 inches or similar) */
        .ticket-container {
            width: 800px;
            background: white;
            border: 1px solid #000;
            padding: 20px;
            font-family: 'Arial', sans-serif;
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            color: black;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 6rem;
            opacity: 0.05;
            font-weight: bold;
            pointer-events: none;
            text-transform: uppercase;
            color: #000;
        }

        /* PRINT MEDIA QUERY - This runs when you hit Ctrl+P */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            /* Hide the print button and any extra UI */
            .no-print {
                display: none !important;
            }
            .ticket-container {
                box-shadow: none;
                border: none; /* Often cleaner without border in print, or keep as desired */
                width: 100%;
                margin: 0;
            }
            /* Force background graphics (if browser supports) for colors */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print mb-6 flex gap-4">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Ticket
        </button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg shadow">
            Close Window
        </button>
    </div>

    <div class="ticket-container">
        
        <div class="watermark"><?= $_SESSION['branch_name'] ?? 'ML PAWN' ?></div>

        <div class="flex justify-between items-start border-b-2 border-black pb-4 mb-4">
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tighter leading-none">ML PAWNSHOP</h1>
                <p class="text-xs font-bold uppercase mt-1">Branch: <?= $_SESSION['branch_name'] ?? 'Main Branch' ?></p>
                <p class="text-[10px] text-gray-600 max-w-[250px] leading-tight mt-1">123 Rizal St., Plaridel, Bulacan<br>TIN: 000-123-456-000<br>Mon-Sun: 8:00 AM - 5:00 PM</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-gray-500">PAWN TICKET NO.</p>
                <p class="text-2xl font-mono font-black text-red-600 tracking-tight"><?= $row['pawn_ticket_number'] ?></p>
                <p class="text-xs font-bold mt-1 text-black">Date Granted: <span class="font-mono"><?= date('M d, Y', strtotime($row['loan_date'])) ?></span></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8">
            
            <div class="space-y-4">
                <div class="border border-black p-3 text-sm relative">
                    <p class="text-[9px] font-bold text-gray-500 uppercase absolute -top-2 bg-white px-1 left-2">Pawner's Name</p>
                    <p class="font-bold uppercase text-black pt-1"><?= $pawner_name ?></p>
                    
                    <div class="mt-3">
                        <p class="text-[9px] font-bold text-gray-500 uppercase">Address</p>
                        <p class="uppercase text-xs leading-tight text-black"><?= $row['address'] ?></p>
                    </div>

                    <div class="mt-2 flex justify-between">
                        <div>
                            <p class="text-[9px] font-bold text-gray-500 uppercase">Contact No.</p>
                            <p class="text-xs text-black"><?= $row['contact_number'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="border border-black p-3 text-sm bg-gray-50">
                    <p class="text-[9px] font-bold text-gray-500 uppercase">ID Presented</p>
                    <p class="font-mono font-bold uppercase text-black text-xs"><?= $row['item_description'] ?></p>
                </div>

                <div class="border-2 border-black p-3 text-center bg-red-50">
                    <div class="grid grid-cols-2 gap-4 divide-x divide-red-200">
                        <div>
                            <p class="text-[9px] font-bold uppercase text-black">Maturity Date</p>
                            <p class="font-bold text-lg text-red-600 leading-none mt-1"><?= date('M d, Y', strtotime($row['due_date'])) ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase text-black">Expiry Date</p>
                            <p class="font-bold text-lg text-red-600 leading-none mt-1"><?= date('M d, Y', strtotime($row['expiry_date'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                
                <div class="border border-black p-3 relative">
                    <p class="text-[9px] font-bold text-gray-500 uppercase absolute -top-2 bg-white px-1 left-2">Description of Pawn</p>
                    <p class="font-bold text-sm uppercase text-black pt-1"><?= $row['item_name'] ?></p>
                    <p class="text-xs text-gray-600 mt-1 italic">Condition: <span class="font-bold not-italic text-black"><?= $row['item_condition'] ?></span></p>
                </div>

                <table class="w-full text-sm border-collapse border border-black">
                    <tr>
                        <td class="border border-black p-1 text-[10px] uppercase bg-gray-100 w-1/2 text-black pl-2">Appraised Value</td>
                        <td class="border border-black p-1 text-right font-bold text-black pr-2">₱ <?= number_format($appraised_value, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="border border-black p-1 text-[10px] uppercase bg-gray-100 text-black pl-2">Principal Loan</td>
                        <td class="border border-black p-1 text-right font-bold text-base text-black pr-2">₱ <?= number_format($row['principal_amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="border border-black p-1 text-[10px] uppercase text-red-600 pl-2">Less: Interest (3%)</td>
                        <td class="border border-black p-1 text-right text-red-600 pr-2">(<?= number_format($row['principal_amount'] * 0.03, 2) ?>)</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-1 text-[10px] uppercase text-red-600 pl-2">Less: Svc Charge</td>
                        <td class="border border-black p-1 text-right text-red-600 pr-2">(<?= number_format($row['service_charge'], 2) ?>)</td>
                    </tr>
                    <tr class="bg-black text-white print:bg-black print:text-white">
                        <td class="border border-black p-2 font-bold uppercase pl-2">Net Proceeds</td>
                        <td class="border border-black p-2 text-right font-black text-lg pr-2">₱ <?= number_format($row['net_proceeds'], 2) ?></td>
                    </tr>
                </table>

                <div class="mt-8 grid grid-cols-2 gap-8 text-center pt-4">
                    <div class="relative">
                        <div class="border-b border-black mb-1"></div>
                        <p class="text-[8px] uppercase font-bold text-black">Pawner's Signature</p>
                    </div>
                    <div class="relative">
                        <div class="border-b border-black mb-1"></div>
                        <p class="text-[8px] uppercase font-bold text-black">Authorized Representative</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 border-t-2 border-dashed border-gray-400 pt-2">
            <p class="text-[8px] text-justify leading-snug text-gray-500">
                <strong>TERMS AND CONDITIONS:</strong> I acknowledge receipt of the loan amount and agree to the terms herein. I understand that if I fail to redeem or renew this pawn before the Expiry Date, the item will be foreclosed and sold at public auction. The interest rate is 3% per month. Penalty interest of 1% per month applies after maturity.
            </p>
            <div class="text-right mt-1">
                <p class="text-[9px] font-bold uppercase text-gray-400">Original Copy</p>
            </div>
        </div>

    </div>

</body>
</html>