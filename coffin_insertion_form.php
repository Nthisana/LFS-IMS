<h2>Coffin Insertion</h2>

<form id="coffinForm"> 
    <label>Branch:</label>
    <select name="branch" id="branch" required>
        <option value="">-- Select Branch --</option>
        <option>Hlotse</option>
        <option>Lithoteng</option>
        <option>Maseru</option>
        <option>Maputsoe</option>
        <option>Mohale's Hoek</option>
        <option>Kolonyama</option>
        <option>Mantsebo</option>
        <option>Majane</option>
        <option>Makhakhe</option>
        <option>Nyakosoba</option>
        <option>Tsakholo</option>
        <option>Pitseng</option>
        <option>Moeketsane</option>
        <option>Rampai</option>
        <option>Marakabei</option>
        <option>Mphorosane</option>
        <option>Maputsoe</option>
        <option>TY</option>
        <option>Thabana Morena</option>
        <option>Quthing</option>
        <option>Mphaki</option>
        <option>Mt. Moorosi</option>
        <option>Qacha's Nek</option>
        <option>Thaba Tseka</option>
        <option>Mafeteng</option>
        <option>Mokhotlong</option>
        <option>Bokong</option>
        <option>Semonkong</option>
    </select>

    <label>Storage:</label>
    <select name="storage" required>
        <option value="">-- Select Storage --</option>
        <option>Show room</option>
        <option>Store room</option>
    </select>

    <label>Arrival Date:</label>
    <input type="date" name="arrival_date">

    <br><br>
    <table id="coffinTable">
        <thead>
            <tr>
                <th>No.</th>
                <th>Coffin Type</th>
                <th>Status</th>
                <th>Transfer Location</th>
                <th>Action Date</th>
                <th>Generated Code</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < 15; $i++): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><input type="text" name="coffin_type[]"></td>
                <td>
                    <select name="status[]">
                        <option>In-stock</option>
                        <option>Transfer</option>
                        <option>Damage</option>
                        <option>Write-off</option>
                        <option>Sold</option>
                    </select>
                </td>
                <td>
                    <select name="transfer_location[]">
                        <option value="">-- Select Location --</option>
                        <option>Hlotse</option>
                        <option>Lithoteng</option>
                        <option>Maseru</option>
                        <option>Maputsoe</option>
                        <option>Mohale's Hoek</option>
                        <option>Kolonyama</option>
                        <option>Mantsebo</option>
                        <option>Majane</option>
                        <option>Makhakhe</option>
                        <option>Nyakosoba</option>
                        <option>Tsakholo</option>
                        <option>Pitseng</option>
                        <option>Moeketsane</option>
                        <option>Rampai</option>
                        <option>Marakabei</option>
                        <option>Mphorosane</option>
                        <option>Maputsoe</option>
                        <option>TY</option>
                        <option>Thabana Morena</option>
                        <option>Quthing</option>
                        <option>Mphaki</option>
                        <option>Mt. Moorosi</option>
                        <option>Qacha's Nek</option>
                        <option>Thaba Tseka</option>
                        <option>Mafeteng</option>
                        <option>Mokhotlong</option>
                        <option>Bokong</option>
                        <option>Semonkong</option>
                    </select>
                </td>
                <td><input type="date" name="action_date[]"></td>
                <td class="codeCell"></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <div class="btns">
        <button type="submit">Submit All</button>
        <button type="button" onclick="clearForm()">Clear Table</button>
    </div>
</form>

<div id="result"></div>

<script>
let lastCodes = [];

document.getElementById('coffinForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const statuses = Array.from(document.getElementsByName("status[]"));
    const transfers = Array.from(document.getElementsByName("transfer_location[]"));
    let hasError = false;

    statuses.forEach((status, index) => {
        if (status.value === "Transfer" && transfers[index].value.trim() === "") {
            alert("Insert Transfer Location in row " + (index + 1));
            hasError = true;
        }
    });

    if (hasError) return;

    const form = e.target;
    const formData = new FormData(form);

    fetch('insert_coffins_ajax.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json())
      .then(data => {
        if (data.success) {
            const codes = data.codes;
            const rows = document.querySelectorAll('#coffinTable tbody tr');

            if (JSON.stringify(codes) === JSON.stringify(lastCodes)) {
                if (!confirm("Duplicate coffin codes, do you wish to erase codes and add same coffins?")) {
                    return;
                }
            }

            lastCodes = [...codes];

            codes.forEach((code, i) => {
                if (code) {
                    rows[i].querySelector('.codeCell').innerText = code;
                }
            });

            if (confirm(`${data.message}\n\nDo you want to clear the form now?`)) {
                clearForm();
            }

        } else {
            document.getElementById('result').innerHTML = `<p style="color:red;">${data.message}</p>`;
        }
    }).catch(() => {
        document.getElementById('result').innerText = "Error submitting data.";
    });
});

function clearForm() {
    document.getElementById('coffinForm').reset();
    document.querySelectorAll('.codeCell').forEach(cell => cell.innerText = '');
    document.getElementById('result').innerText = '';
    lastCodes = [];
}
</script>
