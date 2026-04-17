@if(isset($showPasswordReminder) && $showPasswordReminder)
<div class="password-reminder-alert" style="
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px solid #f39c12;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 4px 15px rgba(243, 156, 18, 0.2);
    position: relative;
    overflow: hidden;
">
    <div style="
        position: absolute;
        top: -10px;
        right: -10px;
        background: #e67e22;
        color: white;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: bold;
    ">
        ⚠️
    </div>
    
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="
            background: #f39c12;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        ">
            🔐
        </div>
        
        <div style="flex: 1;">
            <h4 style="
                margin: 0 0 10px 0;
                color: #d68910;
                font-size: 18px;
                font-weight: 600;
            ">
                Security Alert: Change Your Password
            </h4>
            <p style="
                margin: 0 0 15px 0;
                color: #8b4513;
                line-height: 1.5;
            ">
                You're currently using a temporary password. For your account security, please change it immediately.
            </p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('dashboard.settings') }}#projects__two" style="
                    background: #e67e22;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                " onmouseover="this.style.background='#d68910'" onmouseout="this.style.background='#e67e22'">
                    <span>🔑</span>
                    Change Password Now
                </a>
                <button onclick="dismissPasswordReminder()" style="
                    background: transparent;
                    color: #8b4513;
                    padding: 10px 20px;
                    border: 1px solid #f39c12;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='#f39c12'; this.style.color='white'" onmouseout="this.style.background='transparent'; this.style.color='#8b4513'">
                    Remind Me Later
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function dismissPasswordReminder() {
    const reminder = document.querySelector('.password-reminder-alert');
    if (reminder) {
        reminder.style.opacity = '0';
        reminder.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            reminder.remove();
        }, 300);
    }
}

// Auto-hide after 30 seconds if user doesn't interact
setTimeout(() => {
    const reminder = document.querySelector('.password-reminder-alert');
    if (reminder && !reminder.querySelector(':hover')) {
        reminder.style.opacity = '0.7';
        reminder.style.transform = 'scale(0.98)';
    }
}, 30000);
</script>

<style>
.password-reminder-alert {
    transition: all 0.3s ease;
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.password-reminder-alert:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(243, 156, 18, 0.3);
}

@media (max-width: 768px) {
    .password-reminder-alert {
        padding: 15px;
        margin: 15px 0;
    }
    
    .password-reminder-alert > div {
        flex-direction: column;
        text-align: center;
    }
    
    .password-reminder-alert > div > div:last-child {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
}
</style>
@endif
