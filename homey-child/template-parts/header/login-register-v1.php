<?php
global $homey_local;
?>

<style>
.top-header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

/* Shadow visible on white backgrounds */
.shadow-box {
    box-shadow: 0 3px 12px rgba(0,0,0,0.12);
}

/* EN | £ pill */
.lang-currency-box {
    display: flex;
    align-items: center;
    background: #ffffff;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 14px;
    font-weight: 600;
    border: 1px solid #eaeaea;
}
.lang-currency-box .divider {
    margin: 0 8px;
    opacity: 0.4;
}

/* Round profile button */
.profile-menu-btn {
   width: 68px;
    height: 38px;
    gap: 8px;
    background: #ffffff;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #eaeaea;
    cursor: pointer;
}

.profile-menu-btn svg {
    width: 21px;
    height: 21px;
    stroke: #222;
}
.header-comp-right{
    padding:0px;
}
.top-header-right{
    padding:20px;
    padding-right:10px !important;
}
.top-switchers {
    display: flex;
    align-items: center;
    gap: 12px;
}
</style>

<div class="top-header-right">

    <div class="top-switchers">

        <!-- EN | £ pill -->
        <div class="lang-currency-box shadow-box">
            <span class="lang">EN</span>
            <span class="divider">|</span>
            <span class="currency">£</span>
        </div>

        <!-- Profile Button with stronger shadow -->
        <div class="profile-menu-btn shadow-box" data-toggle="modal" data-target="#modal-login">
<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" fill="none"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path stroke="#535358" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8h15M5 16h22M5 24h22M5 11l3-3-3-3"></path> </g></svg><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M12.12 12.78C12.05 12.77 11.96 12.77 11.88 12.78C10.12 12.72 8.71997 11.28 8.71997 9.50998C8.71997 7.69998 10.18 6.22998 12 6.22998C13.81 6.22998 15.28 7.69998 15.28 9.50998C15.27 11.28 13.88 12.72 12.12 12.78Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M18.74 19.3801C16.96 21.0101 14.6 22.0001 12 22.0001C9.40001 22.0001 7.04001 21.0101 5.26001 19.3801C5.36001 18.4401 5.96001 17.5201 7.03001 16.8001C9.77001 14.9801 14.25 14.9801 16.97 16.8001C18.04 17.5201 18.64 18.4401 18.74 19.3801Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>        </div>

    </div>

</div>
