// State variables
let isPurchased = false;
let isFrontView = true;
let idData = {};
let uploadedImage = null;
let kdata = null; //load from JSON

//base64-encoded Kenyan coat of arms (placeholder)
const coatOfArmsBase64 = 'data:images/coat of arms.jpg;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAABqElEQVR4nO2VPUvDUBTGv5VKoVqLFiqFgoIugrt0cS8eOugFRDR0UKgX8C7eQ3gVBRG8gY6CgoK6i7eioGAH/QW6aFSoP8/7mpycy8zO7My7MztXAGn4j0gA+AlAoY+YAeD/AUA3SAB4AEBmAPgHALpBAsC/AJAjIgD8CwB1QwSABwCkZ0QA+BcA6oYIAH8CoG6IAPAnANQN4wA8ACA9IwLAnwBQNyIA/AkAdcMEgD8BoG5EAPgTANoGdgB4AEB6RgSAfwGgboQA8CcA1A0TADwAID0jAsCfAFA3IgD8CQB1wwSAPwGgbkQA+BMAGgb2APgAQHpGBIA/AaBuiADwJwDUDRMA/AggPQMCA+AGgI0kAP8B8r17uN1u3vN9v3tLA5+ABrA22oGgbQ2A2gYAABjABmAA2oIgbQ2gAA5oGgbQ2gB2gA1oGgbQ2gB+BmAA1oBjABuGgbQ2gAYQ2gYAoAgB2gAA5oBgaQ2gAB1oABCn2gBA6gBA3TABoBBAgBA2jBsABgAAgA+ABgAB1oABCn2gAA/ADg3g3/ADn/AAAA4e4z4e4e4AAA';

//load JSON data
async function loadkdata() {
    try {
        const response = await fetch('kdata.json');
        if (!response.ok) throw new Error('Failed to load json data file');
        kdata = await response.json();
        populateCascadingDropdowns();
        setupAreaSearch();
    } catch (error) {
        console.error(error);
        document.getElementById('purchaseMessage').textContent = 'Administrative data error.';
        document.getElementById('purchaseMessage').className = 'message error';
    }
}

//generate placeholder image based on gender
function generatePlaceholderImage(gender) {
    const canvas = document.createElement('canvas');
    canvas.width = 132;
    canvas.height = 170;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = (gender === 'Male') ? '#4682b4' : (gender === 'Female') ? '#ff69b4' : '#808080';
    ctx.fillRect(0, 0, 132, 170);

    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.arc(66, 50, 30, 0, Math.PI * 2);
    ctx.moveTo(33, 80);
    ctx.lineTo(33, 140);
    ctx.quadraticCurveTo(66, 170, 99, 140);
    ctx.lineTo(99, 80);
    ctx.closePath();
    ctx.fill();

    ctx.fillStyle = '#000000';
    ctx.font = '30px Arial';
    ctx.fillText(gender === 'Male' ? '♂' : gender === 'Female' ? '♀' : '?', 55, 160);

    return canvas;
}

// Generate mock QR code (for demo)
function generateMockQRCode() {
    const canvas = document.createElement('canvas');
    canvas.width = 40;
    canvas.height = 40;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, 40, 40);

    ctx.fillStyle = '#000000';
    for (let i = 0; i < 8; i++) {
        for (let j = 0; j < 8; j++) {
            if (Math.random() > 0.5) {
                ctx.fillRect(i * 5, j * 5, 5, 5);
            }
        }
    }

    ctx.strokeStyle = '#000000';
    ctx.lineWidth = 2;
    ctx.strokeRect(0, 0, 40, 40);

    return canvas;
}

// Draw microtext pattern
function drawMicrotext(ctx, text, x, y, width, height) {
    ctx.font = '4px Poppins';
    ctx.fillStyle = 'rgba(78, 84, 200, 0.3)';
    for (let i = y; i < y + height; i += 6) {
        ctx.fillText(text.repeat(Math.floor(width / 20)), x, i);
    }
}

// Draw hologram effect
function drawHologram(ctx, x, y, width, height) {
    const gradient = ctx.createLinearGradient(x, y, x + width, y + height);
    gradient.addColorStop(0, 'rgba(102, 178, 173, 0.2)');
    gradient.addColorStop(0.5, 'rgba(78, 84, 200, 0.3)');
    gradient.addColorStop(1, 'rgba(255, 255, 255, 0.2)');
    ctx.fillStyle = gradient;
    ctx.fillRect(x, y, width, height);

    ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
    ctx.lineWidth = 0.5;
    for (let i = 0; i < width; i += 10) {
        ctx.beginPath();
        ctx.moveTo(x + i, y);
        ctx.lineTo(x + i, y + height);
        ctx.stroke();
    }
}

// Populate dropdowns
function populateDropdown(id, options, placeholder) {
    const select = document.getElementById(id);
    select.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
    options.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option;
        opt.textContent = option;
        select.appendChild(opt);
    });
}

// Populate cascading dropdowns
function populateCascadingDropdowns() {
    if (!kdata) return;

    const countySelect = document.getElementById('county');
    const districtSelect = document.getElementById('district');
    const divisionSelect = document.getElementById('division');
    const locationSelect = document.getElementById('location');
    const subLocationSelect = document.getElementById('sub-location');
    const issuePlaceSelect = document.getElementById('issue-place');
    const issuePlace2Select = document.getElementById('issue-place2');

    // Populate counties
    populateDropdown('county', kdata.counties.map(c => c.name), 'Select county');

    // Populate places of issue
    populateDropdown('issue-place', kdata.placesOfIssue, 'Select place of issue');
    populateDropdown('issue-place2', kdata.placesOfIssue, 'Select place of issue');

    // County change
    countySelect.addEventListener('change', () => {
        const county = kdata.counties.find(c => c.name === countySelect.value);
        districtSelect.innerHTML = '<option value="" disabled selected>Select district</option>';
        divisionSelect.innerHTML = '<option value="" disabled selected>Select division</option>';
        locationSelect.innerHTML = '<option value="" disabled selected>Select location</option>';
        subLocationSelect.innerHTML = '<option value="" disabled selected>Select sub-location</option>';
        if (county) {
            populateDropdown('district', county.districts.map(d => d.name), 'Select district');
        }
    });

    // District change
    districtSelect.addEventListener('change', () => {
        const county = kdata.counties.find(c => c.name === countySelect.value);
        const district = county?.districts.find(d => d.name === districtSelect.value);
        divisionSelect.innerHTML = '<option value="" disabled selected>Select division</option>';
        locationSelect.innerHTML = '<option value="" disabled selected>Select location</option>';
        subLocationSelect.innerHTML = '<option value="" disabled selected>Select sub-location</option>';
        if (district) {
            populateDropdown('division', district.divisions.map(div => div.name), 'Select division');
        }
    });

    // Division change
    divisionSelect.addEventListener('change', () => {
        const county = kdata.counties.find(c => c.name === countySelect.value);
        const district = county?.districts.find(d => d.name === districtSelect.value);
        const division = district?.divisions.find(div => div.name === divisionSelect.value);
        locationSelect.innerHTML = '<option value="" disabled selected>Select location</option>';
        subLocationSelect.innerHTML = '<option value="" disabled selected>Select sub-location</option>';
        if (division) {
            populateDropdown('location', division.locations.map(loc => loc.name), 'Select location');
        }
    });

    // Location change
    locationSelect.addEventListener('change', () => {
        const county = kdata.counties.find(c => c.name === countySelect.value);
        const district = county?.districts.find(d => d.name === districtSelect.value);
        const division = district?.divisions.find(div => div.name === divisionSelect.value);
        const location = division?.locations.find(loc => loc.name === locationSelect.value);
        subLocationSelect.innerHTML = '<option value="" disabled selected>Select sub-location</option>';
        if (location) {
            populateDropdown('sub-location', location.subLocations, 'Select sub-location');
        }
    });
}

// Add search functionality for county, district, division, location, sub-location
function setupAreaSearch() {
    // Add search input boxes above each dropdown
    const dropdowns = [
        { id: 'county', label: 'County' },
        { id: 'district', label: 'District' },
        { id: 'division', label: 'Division' },
        { id: 'location', label: 'Location' },
        { id: 'sub-location', label: 'Sub-location' }
    ];

    dropdowns.forEach(drop => {
        const select = document.getElementById(drop.id);
        if (!select) return;
        // Avoid duplicate search boxes
        if (select.previousElementSibling && select.previousElementSibling.classList.contains('area-search')) return;

        const searchBox = document.createElement('input');
        searchBox.type = 'text';
        searchBox.placeholder = `Search ${drop.label}...`;
        searchBox.className = 'area-search';
        searchBox.style.marginBottom = '6px';
        searchBox.style.width = '100%';
        searchBox.style.padding = '6px 10px';
        searchBox.style.borderRadius = '6px';
        searchBox.style.border = '1px solid #667ead';
        searchBox.style.fontSize = '1rem';

        select.parentNode.insertBefore(searchBox, select);

        searchBox.addEventListener('input', function () {
            let options = [];
            switch (drop.id) {
                case 'county':
                    options = kdata.counties.map(c => c.name);
                    break;
                case 'district': {
                    const county = kdata.counties.find(c => c.name === document.getElementById('county').value);
                    options = county ? county.districts.map(d => d.name) : [];
                    break;
                }
                case 'division': {
                    const county = kdata.counties.find(c => c.name === document.getElementById('county').value);
                    const district = county?.districts.find(d => d.name === document.getElementById('district').value);
                    options = district ? district.divisions.map(div => div.name) : [];
                    break;
                }
                case 'location': {
                    const county = kdata.counties.find(c => c.name === document.getElementById('county').value);
                    const district = county?.districts.find(d => d.name === document.getElementById('district').value);
                    const division = district?.divisions.find(div => div.name === document.getElementById('division').value);
                    options = division ? division.locations.map(loc => loc.name) : [];
                    break;
                }
                case 'sub-location': {
                    const county = kdata.counties.find(c => c.name === document.getElementById('county').value);
                    const district = county?.districts.find(d => d.name === document.getElementById('district').value);
                    const division = district?.divisions.find(div => div.name === document.getElementById('division').value);
                    const location = division?.locations.find(loc => loc.name === document.getElementById('location').value);
                    options = location ? location.subLocations : [];
                    break;
                }
            }
            const filtered = options.filter(opt => opt.toLowerCase().includes(searchBox.value.toLowerCase()));
            populateDropdown(drop.id, filtered, `Select ${drop.label.toLowerCase()}`);
        });
    });
}

// Generate random serial number (9 digits)
function generateSerialNumber() {
    return Math.floor(100000000 + Math.random() * 900000000).toString();
}

// Generate random ID number (8 digits)
function generateIDNumber() {
    return Math.floor(10000000 + Math.random() * 90000000).toString();
}

// Generate date of issue (today)
function generateDateOfIssue() {
    const today = new Date();
    return today.toISOString().split('T')[0];
}

// Generate date of expiry (10 years from issue)
function generateDateOfExpiry(issueDate) {
    const date = new Date(issueDate);
    date.setFullYear(date.getFullYear() + 10);
    return date.toISOString().split('T')[0];
}

// Discard form inputs
function discard() {
    document.getElementById('searchForm').reset();
    document.getElementById('bindForm').reset();
    document.getElementById('serial').value = '';
    document.getElementById('idno').value = '';
    isPurchased = false;
    isFrontView = true;
    uploadedImage = null;
    document.getElementById('flipButton').disabled = true;
    document.getElementById('downloadButton').disabled = true;
    document.getElementById('purchaseMessage').textContent = '';
    clearCanvas();
    populateCascadingDropdowns();
}

// Clear canvas
function clearCanvas() {
    const canvas = document.getElementById('idCanvas');
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

// Draw front side of ID
function drawFrontID() {
    const canvas = document.getElementById('idCanvas');
    const ctx = canvas.getContext('2d');
    canvas.width = 337;
    canvas.height = 213;

    // Background gradient
    const bgGradient = ctx.createLinearGradient(0, 0, 337, 213);
    bgGradient.addColorStop(0, '#d4ecd4');
    bgGradient.addColorStop(1, '#b2dab2');
    ctx.fillStyle = bgGradient;
    ctx.fillRect(0, 0, 337, 213);

    // Border
    ctx.strokeStyle = '#006400';
    ctx.lineWidth = 3;
    ctx.strokeRect(0, 0, 337, 213);

    // Hologram effect
    drawHologram(ctx, 0, 0, 337, 213);

    // Kenyan coat of arms
    const coatOfArms = new Image();
    coatOfArms.src = coatOfArmsBase64;
    coatOfArms.onload = () => {
        ctx.drawImage(coatOfArms, 150, 10, 32, 32);
    };

    // Header
    ctx.fillStyle = '#000000';
    ctx.font = 'bold 12px Poppins';
    ctx.textAlign = 'center';
    ctx.fillText('REPUBLIC OF KENYA', 168.5, 30);
    ctx.fillText('NATIONAL IDENTITY CARD', 168.5, 45);

    // Photo
    ctx.strokeStyle = '#000000';
    ctx.lineWidth = 1;
    ctx.strokeRect(10, 50, 132, 170);
    if (uploadedImage) {
        ctx.drawImage(uploadedImage, 10, 50, 132, 170);
    } else {
        const placeholderImage = generatePlaceholderImage(idData.gender);
        ctx.drawImage(placeholderImage, 10, 50, 132, 170);
    }

    // Text fields
    ctx.textAlign = 'left';
    ctx.font = 'bold 10px Poppins';
    ctx.fillStyle = '#000000';
    let yPos = 60;
    const xLabel = 150;
    const xValue = 220;

    ctx.fillText('Surname:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.surname || '', xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('Other Names:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(`${idData.fname || ''} ${idData.lname || ''}`.trim(), xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('Gender:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.gender || '', xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('Date of Birth:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.dob || '', xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('Place of Birth:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.placeOfBirth || '', xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('ID Number:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.idno || '', xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('Place of Issue:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.issuePlace2 || '', xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('Date of Issue:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.dateOfIssue || '', xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('Date of Expiry:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.dateOfExpiry || '', xValue, yPos);
    yPos += 20;

    ctx.font = 'bold 10px Poppins';
    ctx.fillText('Serial Number:', xLabel, yPos);
    ctx.font = '10px Poppins';
    ctx.fillText(idData.serial || '', xValue, yPos);

    // Microtext
    drawMicrotext(ctx, 'KENYA ID', 10, 190, 317, 20);

    // Disclaimer
    ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
    ctx.font = 'italic 8px Poppins';
    ctx.textAlign = 'center';
    ctx.fillText('Mock ID for testing only', 168.5, 205);
}

// back side of ID
function drawBackID() {
    const canvas = document.getElementById('idCanvas');
    const ctx = canvas.getContext('2d');
    canvas.width = 337;
    canvas.height = 213;

    // Background gradient
    const bgGradient = ctx.createLinearGradient(0, 0, 337, 213);
    bgGradient.addColorStop(0, '#d4ecd4');
    bgGradient.addColorStop(1, '#b2dab2');
    ctx.fillStyle = bgGradient;
    ctx.fillRect(0, 0, 337, 213);

    // Border
    ctx.strokeStyle = '#006400';
    ctx.lineWidth = 3;
    ctx.strokeRect(0, 0, 337, 213);

    // Hologram effect
    drawHologram(ctx, 0, 0, 337, 213);

    // Mock QR code
    const qrCode = generateMockQRCode();
    ctx.drawImage(qrCode, 20, 20, 40, 40);

    // MRZ
    ctx.fillStyle = '#000000';
    ctx.font = '12px monospace';
    const mrzLine1 = `P<KEN${(idData.surname || '').padEnd(10, '<').slice(0, 10)}<<${(idData.fname || '').padEnd(10, '<').slice(0, 10)}`;
    const mrzLine2 = `${(idData.idno || '').padEnd(9, '')}KEN${(idData.dob || '').replace(/-/g, '')}M${idData.serial || ''}`;
    ctx.fillText(mrzLine1.slice(0, 30), 20, 160);
    ctx.fillText(mrzLine2.slice(0, 30), 20, 180);

    // Flag colors
    ctx.fillStyle = '#000000';
    ctx.fillRect(100, 20, 217, 10);
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(100, 30, 217, 10);
    ctx.fillStyle = '#006400';
    ctx.fillRect(100, 40, 217, 10);
    ctx.fillStyle = '#ff0000';
    ctx.fillRect(100, 50, 217, 10);

    // Microtext
    drawMicrotext(ctx, 'KENYA ID', 20, 100, 297, 17);

}

// Toggle between front and back
function toggleView() {
    isFrontView = !isFrontView;
    const canvas = document.getElementById('idCanvas');
    canvas.classList.add('flipped');
    setTimeout(() => {
        canvas.classList.remove('flipped');
        if (isFrontView) {
            drawFrontID();
        } else {
            drawBackID();
        }
    }, 250);
}

// Download ID
function downloadID() {
    const canvas = document.getElementById('idCanvas');
    const frontDataURL = canvas.toDataURL('image/png');

    drawBackID();
    const backDataURL = canvas.toDataURL('image/png');

    if (isFrontView) {
        drawFrontID();
    }

    const linkFront = document.createElement('a');
    linkFront.href = frontDataURL;
    linkFront.download = 'kenyan_id_front.png';
    linkFront.click();

    const linkBack = document.createElement('a');
    linkBack.href = backDataURL;
    linkBack.download = 'kenyan_id_back.png';
    linkBack.click();
}

// Show spinner
function showSpinner(button) {
    const spinner = button.querySelector('.fa-spinner');
    spinner.classList.remove('hidden');
    button.disabled = true;
}

// Hide spinner
function hideSpinner(button) {
    const spinner = button.querySelector('.fa-spinner');
    spinner.classList.add('hidden');
    button.disabled = false;
}

// Handle file upload
document.getElementById('file2').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (event) => {
            uploadedImage = new Image();
            uploadedImage.src = event.target.result;
            uploadedImage.onload = () => {
                if (isPurchased) {
                    drawFrontID();
                }
            };
        };
        reader.readAsDataURL(file);
    } else {
        uploadedImage = null;
        if (isPurchased) {
            drawFrontID();
        }
    }
});

// Handle search form submission
document.getElementById('searchForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const button = e.target.querySelector('.btn-primary');
    showSpinner(button);
    if (!document.getElementById('searchForm').checkValidity()) {
        document.getElementById('searchForm').reportValidity();
        hideSpinner(button);
        return;
    }
    if (isPurchased) {
        setTimeout(() => {
            drawFrontID();
            hideSpinner(button);
        }, 500);
    } else {
        document.getElementById('purchaseMessage').textContent = 'Please complete the purchase first.';
        document.getElementById('purchaseMessage').className = 'message error';
        hideSpinner(button);
    }
});

// Handle bind form submission
document.getElementById('bindForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const button = e.target.querySelector('.btn-primary');
    showSpinner(button);
    const form = document.getElementById('bindForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        hideSpinner(button);
        return;
    }

    // Collect form data
    const dateOfIssue = generateDateOfIssue();
    idData = {
        serial: generateSerialNumber(),
        surname: document.getElementById('surname').value,
        fname: document.getElementById('fname').value,
        lname: document.getElementById('lname').value,
        gender: document.getElementById('gender').value,
        idno: generateIDNumber(),
        dob: document.getElementById('dob').value,
        placeOfBirth: document.getElementById('sub-location').value || document.getElementById('location').value || 'Unknown',
        issuePlace2: document.getElementById('issue-place2').value,
        dateOfIssue: dateOfIssue,
        dateOfExpiry: generateDateOfExpiry(dateOfIssue)
    };

    // Update readonly fields
    document.getElementById('serial').value = idData.serial;
    document.getElementById('idno').value = idData.idno;

    // Mock purchase
    isPurchased = true;
    document.getElementById('flipButton').disabled = false;
    document.getElementById('downloadButton').disabled = false;
    document.getElementById('purchaseMessage').textContent = 'Purchase Successful! ID generated!';
    document.getElementById('purchaseMessage').className = 'message success';

    // Draw initial front
    setTimeout(() => {
        drawFrontID();
        hideSpinner(button);
    }, 500);
});

// jQuery AJAX for bindForm (if jQuery is loaded)
if (typeof $ !== "undefined") {
    $(document).ready(function() {
        $('#bindForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: 'purchase.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        idData = response.data;
                        isPurchased = true;
                        document.getElementById('flipButton').disabled = false;
                        document.getElementById('downloadButton').disabled = false;
                        document.getElementById('purchaseMessage').textContent = "Generation Successful! Review to purchase.";
                        document.getElementById('purchaseMessage').className = "message success";
                        drawFrontID();
                    } else {
                        document.getElementById('purchaseMessage').textContent = response.message;
                        document.getElementById('purchaseMessage').className = "message error";
                    }
                },
                error: function() {
                    document.getElementById('purchaseMessage').textContent = "An error occurred. Please try again.";
                    document.getElementById('purchaseMessage').className = "message error";
                }
            });
        });
    });
}

// Flip button
document.getElementById('flipButton').addEventListener('click', toggleView);

// Download button
document.getElementById('downloadButton').addEventListener('click', downloadID);

// Initialize
loadkdata();