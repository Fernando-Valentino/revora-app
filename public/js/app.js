/**
 * REVORA - Revenue Estimation, Visualization, Optimization, Reporting, and Analytics
 * Front-end Client Script
 */

// Global Reusable SweetAlert2 Toast Helper
window.showToast = function(icon, title) {
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
        return;
    }
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    return Toast.fire({
        icon: icon,
        title: title
    });
};

document.addEventListener('DOMContentLoaded', () => {
    console.log('REVORA Client Loaded Successfully');

    // Handle temporary action notifications
    const dismissAlerts = document.querySelectorAll('.alert-dismiss');
    dismissAlerts.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.parentElement.remove();
        });
    });

    // Realtime Clock Updater
    const clockDate = document.getElementById('clock-date');
    const clockTime = document.getElementById('clock-time');

    if (clockDate && clockTime) {
        const updateClock = () => {
            const now = new Date();
            
            // Format Date: "Hari, Tanggal Bulan Tahun" in Indonesian (id-ID)
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            const formattedDate = now.toLocaleDateString('id-ID', options);
            
            // Format Time: HH:mm:ss
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const formattedTime = `${hours}:${minutes}:${seconds}`;

            clockDate.textContent = formattedDate;
            clockTime.textContent = formattedTime;
        };

        // Initialize immediately and update every second
        updateClock();
        setInterval(updateClock, 1000);
    }

    // Sidebar Toggle for Mobile / Responsive Layout
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        // Create backdrop if not exists
        let backdrop = document.querySelector('.sidebar-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);
        }
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        });
        
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    }
});
