@extends('layouts.app')

@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('style')
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
<style>
    :root {
        --primary-color: #00bef2;
        --primary-light: #4dd2ff;
        --primary-dark: #0099c8;
        --primary-gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --dark-gradient: linear-gradient(135deg, #434343 0%, #000000 100%);
        --card-shadow: 0 10px 40px rgba(0, 190, 242, 0.1);
        --hover-shadow: 0 20px 60px rgba(0, 190, 242, 0.15);
        --border-radius: 16px;
        --sidebar-width: 280px;
        --nav-height: 70px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e3f7ff 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* Main Layout */
    .app-container {
        display: flex;
        min-height: 100vh;
    }

    /* Left Navigation Sidebar */
    .nav-sidebar {
        width: var(--sidebar-width);
        background: linear-gradient(180deg, #ffffff 0%, #f8fdff 100%);
        border-right: 1px solid rgba(0, 190, 242, 0.1);
        box-shadow: 5px 0 30px rgba(0, 0, 0, 0.05);
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        z-index: 200;
        transition: transform 0.3s ease;
    }

    .sidebar-header {
        padding: 3px;
        border-bottom: 1px solid rgba(0, 190, 242, 0.1);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .logo-icon {
        width: 40px;
        height: 40px;
        background: var(--primary-gradient);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .logo-text {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary-dark);
        white-space: nowrap;
    }

    .nav-menu {
        flex: 1;
        padding: 20px 0;
        overflow-y: auto;
    }

    .nav-item {
        padding: 0 20px;
        margin-bottom: 10px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 20px;
        color: var(--primary-dark);
        text-decoration: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .nav-link:hover {
        background: rgba(0, 190, 242, 0.05);
        color: var(--primary-dark);
        transform: translateX(5px);
    }

    .nav-link.active {
        background: var(--primary-gradient);
        color: white !important;
        box-shadow: 0 5px 20px rgba(0, 190, 242, 0.2);
    }

    .nav-icon {
        font-size: 18px;
        width: 24px;
        text-align: center;
    }

    .nav-text {
        font-size: 15px;
        font-weight: 500;
        white-space: nowrap;
    }

    .nav-badge {
        margin-left: auto;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-size: 12px;
        padding: 2px 10px;
        border-radius: 10px;
        font-weight: 600;
    }

    .nav-link.active .nav-badge {
        background: rgba(255, 255, 255, 0.3);
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid rgba(0, 190, 242, 0.1);
        background: rgba(0, 190, 242, 0.02);
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 16px;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 2px;
    }

    .user-status {
        font-size: 12px;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background: #43e97b;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    /* Main Content Area */
    .main-content-wrapper {
        flex: 1;
        margin-left: var(--sidebar-width);
        padding: 30px;
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }

    /* Content Sections */
    .content-section {
        display: none;
        animation: fadeIn 0.5s ease;
    }

    .content-section.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Page Header */
    .page-header {
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-subtitle {
        color: #666;
        font-size: 16px;
        max-width: 600px;
    }

    /* My Meetings Page */
    .meetings-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 30px;
        margin-bottom: 30px;
    }

    .search-container {
        margin-bottom: 30px;
    }

    .search-box {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .search-input {
        flex: 1;
        padding: 15px 20px;
        border: 2px solid rgba(0, 190, 242, 0.2);
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: rgba(0, 190, 242, 0.05);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 190, 242, 0.1);
        background: white;
    }

    .search-btn {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 15px 30px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 190, 242, 0.3);
    }

    .meetings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
    }

    .meeting-card {
        background: white;
        border: 2px solid rgba(0, 190, 242, 0.1);
        border-radius: 12px;
        padding: 25px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .meeting-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-color);
        box-shadow: 0 15px 40px rgba(0, 190, 242, 0.15);
    }

    .meeting-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .badge-live {
        background: linear-gradient(135deg, #f72585 0%, #ff9e00 100%);
    }

    .badge-upcoming {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .badge-ended {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }

    .meeting-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 10px;
        padding-right: 80px;
    }

    .meeting-description {
        color: #666;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orvent: vertical;
        overflow: hidden;
    }

    .meeting-info {
        display: flex;
        align-items: center;
        gap: 20px;
        font-size: 14px;
        color: #888;
        margin-bottom: 20px;
    }

    .meeting-info-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .meeting-actions {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 190, 242, 0.3);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
    }

    .btn-outline:hover {
        background: rgba(0, 190, 242, 0.1);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-icon {
        font-size: 60px;
        color: rgba(0, 190, 242, 0.2);
        margin-bottom: 20px;
    }

    .empty-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 10px;
    }

    /* Create Meeting Page */
    .form-container {
        max-width: 800px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 40px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--primary-dark);
        font-size: 15px;
    }

    .form-control {
        width: 100%;
        padding: 5px 20px;
        border: 2px solid rgba(0, 190, 242, 0.2);
        border-radius: 12px 12px 12px 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: rgba(0, 190, 242, 0.05);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 190, 242, 0.1);
        background: white;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* Join Meeting Page */
    .join-container {
        max-width: 600px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 40px;
        /* margin: 0; */
    }

    .input-group {
        display: flex;
        gap: 15px;
        margin: 30px 0;
    }

    .input-group input {
        flex: 1;
    }

    /* Personal Room Page */
    .room-container {
        max-width: 800px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 40px;
    }

    .room-link {
        display: flex;
        gap: 15px;
        margin: 25px 0;
    }

    .link-display {
        flex: 1;
        background: rgba(0, 190, 242, 0.05);
        padding: 15px 20px;
        border-radius: 12px;
        font-family: monospace;
        font-size: 16px;
        border: 2px solid rgba(0, 190, 242, 0.1);
        overflow-x: auto;
        min-width: 0;
        word-break: break-all;
    }

    /* Settings Page */
    .settings-container {
        max-width: 800px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 40px;
    }

    /* Mobile Toggle Button */
    .sidebar-toggle {
        position: absolute;
        top: 12px;
        z-index: 1100;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 10px;
        width: 40px;
        height: 40px;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        cursor: pointer;
        box-shadow: 0 5px 20px rgba(0, 190, 242, 0.3);
        transition: all 0.3s ease;
    }
    @media (max-width: 768px) {
        .nav-sidebar .sidebar-header {
            padding: 24px 0px 32px 0px !important;
        }
    }

    html[dir="rtl"] .sidebar-toggle {
        right: 32px;
        left: auto;
    }

    html[dir="ltr"] .sidebar-toggle {
        left: 15px;
        right: auto;
    }

    .sidebar-toggle:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 190, 242, 0.4);
    }

    @media (max-width: 768px) {
        .sidebar-toggle {
            top: 8px;
        }

        html[dir="ltr"] .sidebar-toggle {
            right: 14px;
        }

        html[dir="rtl"] .sidebar-toggle {
            left: 14px;
        }
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .nav-sidebar {
            transform: translateX(-100%);
            z-index: 1000;
        }

        .nav-sidebar.active {
            transform: translateX(0);
        }

        .main-content-wrapper {
            margin-left: 0;
        }

        .sidebar-toggle {
            display: flex;
        }
    }

    @media (max-width: 992px) {
        .meetings-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .page-title {
            font-size: 28px;
        }
    }

    @media (max-width: 768px) {
        .main-content-wrapper {
            padding: 20px;
        }

        .meetings-grid {
            grid-template-columns: 1fr;
        }

        .search-box {
            flex-direction: column;
        }

        .search-btn {
            width: 100%;
            justify-content: center;
        }

        .input-group {
            flex-direction: column;
        }

        .room-link {
            flex-direction: column;
        }
    }

    @media (max-width: 576px) {
        .main-content-wrapper {
            padding: 15px;
        }

        .meetings-container,
        .form-container,
        .join-container,
        .room-container,
        .settings-container {
            padding: 25px;
        }

        .page-title {
            font-size: 24px;
        }
    }

    /* Meetings List Vertical Layout */
    .meetings-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .meeting-list-item {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0, 190, 242, 0.1);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .meeting-item {
        background: white;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0, 190, 242, 0.1);
        padding: 20px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
    }

    .meeting-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--hover-shadow);
        border-color: var(--primary-color);
    }

    .meeting-item-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        min-width: 80px;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-today {
        background: linear-gradient(135deg, #f72585 0%, #ff9e00 100%);
        color: white;
    }

    .status-upcoming {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .status-ended {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    .meeting-time {
        font-size: 13px;
        color: #666;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .meeting-time i {
        font-size: 11px;
        color: var(--primary-color);
    }

    .meeting-item-body {
        flex: 1;
        min-width: 0;
        /* Allows text truncation */
    }

    .meeting-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .meeting-meta {
        display: flex;
        gap: 15px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .meta-item {
        font-size: 13px;
        color: #666;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .meta-item i {
        font-size: 12px;
        color: var(--primary-color);
    }

    .meeting-description {
        font-size: 13px;
        color: #777;
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .meeting-item-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-primary-small,
    .btn-outline-small {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary-small {
        background: var(--primary-gradient);
        color: white;
    }

    .btn-primary-small:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 190, 242, 0.3);
    }

    .btn-outline-small {
        background: transparent;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
    }

    .btn-outline-small:hover {
        background: rgba(0, 190, 242, 0.1);
        transform: translateY(-2px);
    }

    .password-indicator {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 190, 242, 0.1);
        color: var(--primary-color);
        font-size: 14px;
        cursor: help;
    }

    /* Responsive Design for Compact Meetings */
    @media (max-width: 768px) {
        .meeting-item {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
            padding: 15px;
        }

        .meeting-item-header {
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
            min-width: auto;
        }

        .meeting-item-actions {
            width: 100%;
            justify-content: center;
            padding-top: 10px;
            border-top: 1px solid rgba(0, 190, 242, 0.1);
        }

        .meeting-title {
            white-space: normal;
            font-size: 15px;
        }

        .meeting-meta {
            gap: 10px;
        }
    }

    @media (max-width: 480px) {
        .meeting-meta {
            flex-direction: column;
            gap: 5px;
        }

        .status-badge {
            font-size: 10px;
            padding: 3px 8px;
        }

        .btn-primary-small,
        .btn-outline-small,
        .password-indicator {
            width: 32px;
            height: 32px;
            font-size: 13px;
        }
    }

    .meeting-list-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--hover-shadow);
        border-color: var(--primary-color);
    }

    .meeting-list-header {
        background: linear-gradient(135deg, rgba(0, 190, 242, 0.05) 0%, rgba(0, 190, 242, 0.02) 100%);
        padding: 15px 25px;
        border-bottom: 1px solid rgba(0, 190, 242, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .meeting-status-indicator {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-dot.status-live {
        background: linear-gradient(135deg, #f72585 0%, #ff9e00 100%);
        box-shadow: 0 0 10px rgba(247, 37, 133, 0.5);
        animation: pulse 2s infinite;
    }

    .status-dot.status-upcoming {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        box-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
    }

    .status-dot.status-ended {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }

        100% {
            opacity: 1;
        }
    }

    .status-text {
        font-size: 13px;
        font-weight: 600;
        color: var(--primary-dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .meeting-list-actions {
        display: flex;
        gap: 8px;
    }

    .icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: white;
        border: 1px solid rgba(0, 190, 242, 0.2);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .icon-btn:hover {
        background: var(--primary-gradient);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 190, 242, 0.3);
        border-color: var(--primary-color);
    }

    .icon-btn.start-btn {
        background: var(--primary-gradient);
        color: white;
        border: none;
    }

    .icon-btn.start-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px) scale(1.05);
    }

    .meeting-list-body {
        padding: 25px;
    }

    .meeting-list-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .meeting-list-title i {
        color: var(--primary-color);
        font-size: 18px;
    }

    .meeting-list-description {
        color: #666;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(0, 190, 242, 0.1);
    }

    .meeting-list-details {
        background: rgba(0, 190, 242, 0.02);
        border-radius: 10px;
        padding: 20px;
        border: 1px solid rgba(0, 190, 242, 0.1);
    }

    .detail-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }

    .detail-row:last-child {
        margin-bottom: 0;
    }

    .detail-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }

    .detail-item i {
        width: 20px;
        color: var(--primary-color);
        font-size: 14px;
        text-align: center;
    }

    .detail-label {
        font-weight: 600;
        color: var(--primary-dark);
        min-width: 80px;
    }

    .detail-value {
        color: #555;
        font-weight: 500;
        flex: 1;
    }

    .meeting-list-footer {
        padding: 20px 25px;
        background: linear-gradient(135deg, rgba(0, 190, 242, 0.03) 0%, rgba(0, 190, 242, 0.01) 100%);
        border-top: 1px solid rgba(0, 190, 242, 0.1);
    }

    .meeting-footer-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 15px;
    }

    .meeting-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .meta-item {
        font-size: 13px;
        color: #777;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .meta-item i {
        font-size: 12px;
    }

    .pagination-container {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(0, 190, 242, 0.1);
        text-align: center;
    }

    /* Responsive Design for Meetings List */
    @media (max-width: 768px) {
        .detail-row {
            flex-direction: column;
            gap: 10px;
        }

        .detail-item {
            flex-direction: row;
            align-items: flex-start;
        }

        .detail-label {
            min-width: 70px;
        }

        .meeting-footer-actions {
            flex-direction: column;
        }

        .meeting-footer-actions .action-btn {
            width: 100%;
            justify-content: center;
        }

        .meeting-meta {
            flex-direction: column;
            gap: 8px;
        }
    }

    @media (max-width: 576px) {
        .meeting-list-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .meeting-list-actions {
            width: 100%;
            justify-content: flex-end;
        }

        .meeting-list-body {
            padding: 20px;
        }

        .meeting-list-title {
            font-size: 19px;
        }
    }

    .navbar {
        padding-top: 0px !important;

    }

    /* ??????? - ??? ????? ?????? */
    .recordings-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 20px;
    }

    .recording-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid var(--primary-color);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s ease;
    }

    .recording-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .recording-item-header {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
    }

    .recording-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .recording-details {
        flex: 1;
    }

    .recording-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .recording-date {
        font-size: 14px;
        color: #666;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .recording-id {
        font-size: 12px;
        color: #888;
        background: #f5f5f5;
        padding: 2px 8px;
        border-radius: 4px;
        margin-top: 3px;
    }

    .recording-actions {
        display: flex;
        gap: 10px;
    }

    .btn-play {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-play:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(74, 108, 247, 0.4);
    }
@media (max-width: 768px){

    .sidebar-header{
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .navbar-brand{
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        margin: 0 !important;
    }

}
    .btn-download {
        background: #f0f0f0;
        color: #333;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-download:hover {
        background: #e0e0e0;
    }

    /* ????? */
    .search-container {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    
    @media (max-width: 768px) {
        .recording-item {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
            padding: 15px;
        }
        .recording-item-header {
            width: 100%;
        }
        .recording-title {
            font-size: 14px;
            word-break: break-all;
            white-space: normal;
        }
        .recording-actions {
            width: 100%;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding-top: 12px;
            border-top: 1px solid rgba(0, 190, 242, 0.1);
        }
        .recording-actions button {
            flex: 1;
            justify-content: center;
            font-size: 13px;
            padding: 10px 8px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 8px;
        }
        
        .recording-actions .btn-copy,
        .recording-actions .btn-delete {
            flex: 0 0 40px;
            padding: 0;
            justify-content: center;
        }
    }
    .search-input-wrapper {
        flex: 1;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s;
    }

    .search-input:focus {
        border-color: var(--primary-color);
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .btn-copy {
        padding: 6px 10px;
        background: #555;
        color: #fff;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-copy:hover {
        background: #333;
    }


    .btn-delete {
        background: #dc2626;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.2s;
    }

    .btn-delete:hover {
        background: #b91c1c;
    }
</style>
@endsection

@section('content')

<script>
    // Global delegated event listener for delete buttons
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.np-delete-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();

        var id = btn.getAttribute('data-rec-id');
        var name = btn.getAttribute('data-rec-name');

        if (!confirm('Delete recording: ' + name + '?\n\nThis cannot be undone.')) return;

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var token = csrfMeta ? csrfMeta.content : '';

        btn.disabled = true;
        btn.style.opacity = '0.5';

        fetch('/recordings/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    // Hide the deleted item visually
                    var item = btn.closest('.recording-item');
                    if (item) item.style.display = 'none';
                    else location.reload();
                } else {
                    alert('Failed: ' + (data.error || 'Unknown'));
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            })
            .catch(function (err) {
                alert('Network error: ' + err.message);
                btn.disabled = false;
                btn.style.opacity = '1';
            });
    }, true);
</script>


<script>
    window.deleteRecording = function (id, name) {
        if (!confirm('Are you sure you want to delete this recording?\n\n' + name + '\n\nThis action cannot be undone.')) return;
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = csrfMeta ? csrfMeta.content : '';
        fetch('/recordings/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    if (typeof loadRecordings === 'function') {
                        try { loadRecordings(); return; } catch (e) { }
                    }
                    location.reload();
                } else {
                    alert('Failed to delete: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(function (err) { alert('Error: ' + err.message); });
    };
</script>

<div class="app-container">
    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Left Navigation Sidebar -->
    <nav class="nav-sidebar" id="navSidebar">
        <div class="sidebar-header">
            <a class="navbar-brand" href="https://netmeet.info">
                <img src="https://netmeet.info/storage/images/PRIMARY_LOGO.png" alt="Net Meet" class="logo-inner">
            </a>
        </div>

        <div class="nav-menu">
            <div class="nav-item">
                <a href="#" class="nav-link active" data-content="my-meetings">
                    <i class="fa fa-calendar-alt nav-icon"></i>
                    <span class="nav-text">My Meetings</span>
                    <span class="nav-badge">{{ $allMeetings->count() ?? 0 }}</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="#" class="nav-link" data-content="create-meeting">
                    <i class="fa fa-plus-circle nav-icon"></i>
                    <span class="nav-text">Create Meeting</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="#" class="nav-link" data-content="personal-room">
                    <i class="fa fa-door-open nav-icon"></i>
                    <span class="nav-text">Personal Room</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="#" class="nav-link" data-content="instant-meeting">
                    <i class="fa fa-bolt nav-icon"></i>
                    <span class="nav-text">Instant Meeting</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="#" class="nav-link" data-content="join-meeting">
                    <i class="fa fa-sign-in-alt nav-icon"></i>
                    <span class="nav-text">Join Meeting</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="#" class="nav-link" data-content="recordings">
                    <i class="fa fa-play-circle nav-icon"></i>
                    <span class="nav-text">Recordings</span>
                    <span class="nav-badge" id="recordingsCount">0</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="#" class="nav-link" data-content="settings">
                    <i class="fa fa-cog nav-icon"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </div>
        </div>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="user-status">
                        <span class="status-dot"></span>
                        <span>Online</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content-wrapper">
        <!-- My Meetings Content -->
        <div class="content-section active" id="my-meetings-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa fa-calendar-alt"></i>
                    My Meetings
                </h1>
                <p class="page-subtitle">Manage and schedule all your meetings</p>
            </div>

            <div class="meetings-container">
                <div class="search-container">
                    <form id="searchMeeting" action="/dashboard">
                        <div class="search-box">
                            <input name="search" type="text" class="search-input" placeholder="Search meetings..."
                                autocomplete="off" maxlength="50" value="{{ $search }}" />
                            <button type="submit" class="search-btn">
                                <i class="fa fa-search"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>

                <div class="meetings-list">
                    @if (isset($allMeetings) && $allMeetings->count() > 0)
                    @foreach ($allMeetings as $key => $value)
                    <div class="meeting-item" data-id="{{ $value->id }}">
                        <div class="meeting-item-header">
                            <div class="meeting-status">
                                @if($value->date == date('Y-m-d'))
                                <span class="status-badge status-today">Today</span>
                                @elseif($value->date > date('Y-m-d'))
                                <span class="status-badge status-upcoming">Upcoming</span>
                                @else
                                <span class="status-badge status-ended">Past</span>
                                @endif
                            </div>

                            <div class="meeting-time">
                                <i class="fa fa-clock"></i>
                                {{ $value->time ? formatTime($value->time) : '--:--' }}
                            </div>
                        </div>

                        <div class="meeting-item-body">
                            <h4 class="meeting-title">
                                {{ $value->title }}
                            </h4>

                            <div class="meeting-meta">
                                <span class="meta-item">
                                    <i class="fa fa-calendar"></i>
                                    {{ $value->date ? formatDate($value->date) : 'No date' }}
                                </span>
                                <span class="meta-item">
                                    <i class="fa fa-id-card"></i>
                                    ID: {{ substr($value->meeting_id, 0, 8) }}...
                                </span>
                            </div>

                            @if($value->description)
                            <p class="meeting-description">
                                {{ strlen($value->description) > 80 ? substr($value->description, 0, 80) . '...' :
                                $value->description }}
                            </p>
                            @endif
                        </div>

                        <div class="meeting-item-actions">
                            <a href="meeting/{{ $value->meeting_id }}" class="btn-primary-small" title="Start Meeting">
                                <i class="fa fa-play"></i>
                            </a>
                            <button class="btn-outline-small copy-link-btn"
                                data-link="{{ route('meeting', ['id' => $value->meeting_id]) }}" title="Copy Link">
                                <i class="fa fa-copy"></i>
                            </button>
                            @if($value->password)
                            <span class="password-indicator" title="Password Protected">
                                <i class="fa fa-lock"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fa fa-calendar-plus"></i>
                        </div>
                        <h3 class="empty-title">No Meetings Found</h3>
                        <p>Start by creating your first meeting!</p>
                        <button class="search-btn" onclick="switchContent('create-meeting')" style="margin-top: 20px;">
                            <i class="fa fa-plus-circle"></i>
                            Create First Meeting
                        </button>
                    </div>
                    @endif
                </div>

                @if ($meetings->hasPages())
                <div class="pagination-container">
                    {{ $meetings->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Create Meeting Content -->
        <!-- Create Meeting Content -->
        <div class="content-section" id="create-meeting-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa fa-plus-circle"></i>
                    Create New Meeting
                </h1>
                <p class="page-subtitle">Schedule a new meeting with advanced options</p>
            </div>

            <div class="form-container">
                <form id="meetingsForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Meeting Title *</label>
                        <input id="title" type="text" class="form-control" name="title" required
                            placeholder="Enter meeting title" maxlength="100" />
                        <small class="form-text text-muted">Enter a descriptive title for your meeting</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea id="description" class="form-control" name="description" rows="3"
                            placeholder="Enter meeting description" maxlength="1000"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password (Optional)</label>
                        <input id="password" type="text" class="form-control" name="password"
                            placeholder="Enter meeting password" maxlength="8" />
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date *</label>
                            <input id="date" type="date" class="form-control" name="date" min="{{ date('Y-m-d') }}"
                                required>
                            <small class="form-text text-muted">Select a date today or in the future</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Time *</label>
                            <input id="time" type="time" class="form-control" name="time" required />
                            <small class="form-text text-muted">Select the meeting start time</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Timezone</label>
                        <select class="form-control" id="timezone" name="timezone">
                            <option value="">{{ __('Select meeting timezone') }}</option>
                            @foreach ($timezones as $timezone)
                            <option value="{{ $timezone['value'] }}">{{ $timezone['value'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" id="meetingsFormId" name="meeting_id" />
                    <div class="form-actions">
                        <button type="submit" class="search-btn" style="width: 100%; justify-content: center;"
                            id="saveNew">
                            <i class="fa fa-calendar-plus"></i>
                            Create Meeting
                        </button>
                        <button type="button" class="search-btn btn-outline" onclick="switchContent('my-meetings')"
                            style="width: 100%; justify-content: center; margin-top: 10px;">
                            <i class="fa fa-times"></i>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Personal Room Content -->
        <div class="content-section" id="personal-room-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa fa-door-open"></i>
                    My Personal Room
                </h1>
                <p class="page-subtitle">Your permanent personal meeting link</p>
            </div>

            <div class="room-container">
                <p>This is your personal permanent meeting link. You can share it with others to join you anytime.</p>

                <div class="room-link">
                    <div class="link-display" id="personalLink">
                        {{ route('meeting', ['id' => auth()->user()->username]) }}
                    </div>
                    <button class="search-btn" id="copyPersonalLink">
                        <i class="fa fa-copy"></i>
                        Copy
                    </button>
                </div>

                <div style="margin-top: 40px;">
                    <h3 style="margin-bottom: 20px; color: var(--primary-dark);">Personal Room Settings</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Enable Password</label>
                            <select class="form-control">
                                <option>Disabled</option>
                                <option>Enabled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Recording</label>
                            <select class="form-control">
                                <option>Automatic</option>
                                <option>Manual</option>
                                <option>Disabled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button class="search-btn"
                    onclick="location.href='{{ route('meeting', ['id' => auth()->user()->username]) }}'"
                    style="margin-top: 30px; width: 100%; justify-content: center;">
                    <i class="fa fa-door-open"></i>
                    Enter Room
                </button>
            </div>
        </div>

        <!-- Instant Meeting Content -->
        <div class="content-section" id="instant-meeting-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa fa-bolt"></i>
                    Instant Meeting
                </h1>
                <p class="page-subtitle">Start a meeting instantly without any setup</p>
            </div>

            <div class="join-container" style="text-align: center;">
                <div style="font-size: 80px; color: var(--primary-color); margin-bottom: 20px;">
                    <i class="fa fa-bolt"></i>
                </div>
                <h2 style="margin-bottom: 20px; color: var(--primary-dark);">Ready to start?</h2>
                <p style="color: #666; margin-bottom: 30px; font-size: 16px;">
                    An instant meeting will be created that anyone can join using the generated link.
                </p>
                <button class="search-btn"
                    onclick="location.href='{{ route('meeting', ['id' => auth()->user()->username]) }}'"
                    style="padding: 20px 40px; font-size: 18px;">
                    <i class="fa fa-video"></i>
                    Start Instant Meeting
                </button>
            </div>
        </div>

        <!-- Join Meeting Content -->
        <div class="content-section" id="join-meeting-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa fa-sign-in-alt"></i>
                    Join Meeting
                </h1>
                <p class="page-subtitle">Join a meeting using the meeting ID</p>
            </div>

            <div class="join-container">
                <p style="margin-bottom: 20px; font-size: 16px;">Enter the 9-digit meeting ID to join an existing
                    meeting</p>

                <div class="input-group">
                    <input type="text" id="joinMeetingId" class="form-control" placeholder="Example: 123456789"
                        maxlength="9">
                    <button class="search-btn" id="joinMeetingBtn">
                        <i class="fa fa-sign-in-alt"></i>
                        Join Now
                    </button>
                </div>

                <p style="font-size: 14px; color: #666; margin-top: 10px;">
                    <i class="fa fa-info-circle"></i> You can find the meeting ID in the email invitation or meeting
                    link
                </p>
            </div>
        </div>

        <!-- Recordings Content -->
        <!-- Recordings Content -->
        <div class="content-section" id="recordings-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa fa-play-circle"></i>
                    Recordings
                </h1>
                <p class="page-subtitle">Manage all your meeting recordings</p>

                <div class="search-container">
                    <div class="search-input-wrapper">
                        <i class="fa fa-search search-icon"></i>
                        <input type="text" id="searchRecordings" placeholder="Search recordings..."
                            class="search-input">
                    </div>
                    <button class="search-btn" id="searchBtn">
                        <i class="fa fa-search"></i>
                        search
                    </button>
                </div>
            </div>

            <div class="meetings-container">
                <div id="recordingsList" class="recordings-list">
                    <!-- ????????? ????? ??? -->
                </div>

                <div class="empty-state" id="emptyState">
                    <div class="empty-icon">
                        <i class="fa fa-play-circle"></i>
                    </div>
                    <h3 class="empty-title">No Recordings Found</h3>
                    <p>Your meeting recordings will appear here</p>
                </div>
            </div>
        </div>
        <!-- Settings Content -->
        <div class="content-section" id="settings-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa fa-cog"></i>
                    Settings
                </h1>
                <p class="page-subtitle">Customize your account settings</p>
            </div>

            <div class="settings-container">
                <div style="margin-bottom: 40px;">
                    <h3 style="margin-bottom: 25px; color: var(--primary-dark);">Account Settings</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->username }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ auth()->user()->email }}">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 40px;">
                    <h3 style="margin-bottom: 25px; color: var(--primary-dark);">Meeting Settings</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Enable Auto Join</label>
                            <select class="form-control">
                                <option>Yes</option>
                                <option>No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Enable Recording</label>
                            <select class="form-control">
                                <option>Yes</option>
                                <option>No</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button class="search-btn" style="width: 100%; justify-content: center;">
                    <i class="fa fa-save"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Modals (Keep existing modals if needed) -->
@endsection

@section('script')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        // Navigation functionality
        const navLinks = document.querySelectorAll('.nav-link');
        const contentSections = document.querySelectorAll('.content-section');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const navSidebar = document.getElementById('navSidebar');

        // Function to switch content
        function switchContent(targetId) {
            // Remove active class from all links
            navLinks.forEach(link => {
                link.classList.remove('active');
            });

            // Add active class to clicked link
            const activeLink = document.querySelector(`.nav-link[data-content="${targetId}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }

            // Hide all content sections
            contentSections.forEach(section => {
                section.classList.remove('active');
            });

            // Show target content section
            const targetElement = document.getElementById(targetId + '-content');
            if (targetElement) {
                targetElement.classList.add('active');
                // Load recordings if navigating to recordings tab
                if (targetId === 'recordings') {
                    loadRecordings();
                }
            }

            // Close sidebar on mobile
            if (window.innerWidth <= 1200) {
                navSidebar.classList.remove('active');
            }
        }

        // Add click event to navigation links
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const targetContent = this.getAttribute('data-content');
                switchContent(targetContent);
            });
        });

        // Mobile sidebar toggle
        if (sidebarToggle && navSidebar) {
            sidebarToggle.addEventListener('click', function () {
                navSidebar.classList.toggle('active');
                const icon = this.querySelector('i');
                if (navSidebar.classList.contains('active')) {
                    icon.className = 'fa fa-times';
                } else {
                    icon.className = 'fa fa-bars';
                }
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 1200) {
                if (!navSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    navSidebar.classList.remove('active');
                    sidebarToggle.querySelector('i').className = 'fa fa-bars';
                }
            }
        });

        // Copy personal link functionality
        const copyPersonalLink = document.getElementById('copyPersonalLink');
        if (copyPersonalLink) {
            copyPersonalLink.addEventListener('click', function () {
                const link = document.getElementById('personalLink').textContent;
                navigator.clipboard.writeText(link).then(() => {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fa fa-check"></i> copied!';
                    this.style.background = 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)';

                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.background = '';
                    }, 2000);
                });
            });
        }

        // Copy meeting link functionality
        document.querySelectorAll('.copy-link-btn').forEach(button => {
            button.addEventListener('click', function () {
                const link = this.getAttribute('data-link');
                navigator.clipboard.writeText(link).then(() => {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fa fa-check"></i>copied!';
                    this.style.background = 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)';

                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.background = '';
                    }, 2000);
                });
            });
        });

        // Join meeting functionality
        const joinMeetingBtn = document.getElementById('joinMeetingBtn');
        const joinMeetingId = document.getElementById('joinMeetingId');

        if (joinMeetingBtn && joinMeetingId) {
            joinMeetingBtn.addEventListener('click', function () {
                const meetingId = joinMeetingId.value.trim();
                if (meetingId.length === 9 && /^\d+$/.test(meetingId)) {
                    window.location.href = '/meeting/' + meetingId;
                } else {
                    alert('Please enter a valid 9-digit meeting ID.');
                    joinMeetingId.focus();
                }
            });

            joinMeetingId.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    joinMeetingBtn.click();
                }
            });
        }

        // Create meeting form
        // const meetingsForm = document.getElementById('meetingsForm');
        // if (meetingsForm) {
        //     meetingsForm.addEventListener('submit', function(e) {
        //         e.preventDefault();
        //         // In a real app, you would submit the form via AJAX
        //         alert('The meeting will be created. This is just an example.');
        //         // Reset form
        //         this.reset();
        //     });
        // }

        // Auto-format meeting ID for join
        if (joinMeetingId) {
            joinMeetingId.addEventListener('input', function (e) {
                let value = e.target.value.replace(/[^0-9]/g, '');
                let formatted = value.match(/.{1,3}/g)?.join('-') ?? '';
                e.target.value = formatted;
            });
        }

        // Initialize based on URL hash
        function initFromHash() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const targetLink = document.querySelector(`.nav-link[data-content="${hash}"]`);
                if (targetLink) {
                    targetLink.click();
                }
            }
        }

        initFromHash();

        // Update hash when clicking links (skip real navigation links)
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                const targetContent = this.getAttribute('data-content');
                if (!targetContent) {
                    return;
                }
                window.history.pushState(null, null, `#${targetContent}`);
            });
        });

        // Handle browser back/forward
        window.addEventListener('popstate', initFromHash);

        // Check window size on load and resize
        function checkWindowSize() {
            if (window.innerWidth > 1200) {
                sidebarToggle.style.display = 'none';
                navSidebar.classList.add('active');
            } else {
                sidebarToggle.style.display = 'flex';
                navSidebar.classList.remove('active');
                sidebarToggle.querySelector('i').className = 'fa fa-bars';
            }
        }

        checkWindowSize();
        window.addEventListener('resize', checkWindowSize);
    });

    // generate a random meeting ID
    function generateMeetingId() {
        return Math.random().toString(36).substr(2, 9);
    }

    // set meeting ID automatically when opening Create Meeting page
    function initMeetingCreation() {
        let meetingId = generateMeetingId();
        $("#meetingsFormId").val(meetingId);
    }

    // call the function when switching to Create Meeting section
    $(document).on("click", "#saveNew", function () {
        initMeetingCreation();
    });

    // OR call it immediately when page loads (if the form is visible)
    initMeetingCreation();
    document.addEventListener("DOMContentLoaded", function () {
        const username = localStorage.getItem("pendingUsername");
        const link = document.getElementById("recordedLink");

        if (!link) return;

        if (username) {
            link.href = "/user-recordings?username=" + encodeURIComponent(username);
        } else {
            link.href = "/user-recordings";
        }

        // When clicking the new Recordings tab ? redirect
        $(document).on("click", '[data-content="recordings"]', function () {
            window.location.href = link.href;
        });
    });
    // Function to load recordings
    // Function to load recordings
    function loadRecordings() {
        const username = localStorage.getItem('pendingUsername') || '{{ auth()->user()->username ?? "Guest" }}';
        const container = document.getElementById('recordingsList');
        const emptyState = document.getElementById('emptyState');
        const searchInput = document.getElementById('searchRecordings');

        // Clear container
        container.innerHTML = '';

        // Show loading state
        emptyState.style.display = 'block';
        emptyState.innerHTML = `
        <div class="empty-icon">
            <i class="fa fa-spinner fa-spin"></i>
        </div>
        <h3 class="empty-title">Loading Recordings...</h3>
    `;

        fetch(`/user-recordings?username=${encodeURIComponent(username)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok' && data.data.length > 0) {
                    emptyState.style.display = 'none';

                    // Store recordings for search
                    window.allRecordings = data.data;

                    renderRecordings(data.data);

                    // Add search functionality
                    searchInput.addEventListener('input', function (e) {
                        const searchTerm = e.target.value.toLowerCase();
                        const filtered = window.allRecordings.filter(recording =>
                            recording.file_name.toLowerCase().includes(searchTerm) ||
                            recording.recorded_at.toLowerCase().includes(searchTerm)
                        );
                        renderRecordings(filtered);

                        // Show empty state if no results
                        if (filtered.length === 0 && searchTerm) {
                            container.innerHTML = '';
                            emptyState.style.display = 'block';
                            emptyState.innerHTML = `
                            <div class="empty-icon">
                                <i class="fa fa-search"></i>
                            </div>
                            <h3 class="empty-title">No matching recordings</h3>
                            <p>Try a different search term</p>
                        `;
                        }
                    });

                } else {
                    emptyState.style.display = 'block';
                    emptyState.innerHTML = `
                    <div class="empty-icon">
                        <i class="fa fa-play-circle"></i>
                    </div>
                    <h3 class="empty-title">No Recordings Found</h3>
                    <p>Your meeting recordings will appear here</p>
                    <button class="btn-play" onclick="startRecordingDemo()" style="margin-top: 20px;">
                        <i class="fa fa-play"></i> Start a Meeting to Record
                    </button>
                `;
                }
            })
            .catch(err => {
                console.error('Failed to load recordings', err);
                emptyState.style.display = 'block';
                emptyState.innerHTML = `
                <div class="empty-icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <h3 class="empty-title">Error Loading Recordings</h3>
                <p>Please try again later</p>
                <button class="btn-play" onclick="loadRecordings()" style="margin-top: 20px;">
                    <i class="fa fa-redo"></i> Retry
                </button>
            `;
            });
    }

    // Helper function to render recordings
    // Helper function to render recordings
    function renderRecordings(recordings) {
        const container = document.getElementById('recordingsList');
        const emptyState = document.getElementById('emptyState');

        container.innerHTML = '';

        if (recordings.length === 0) return;

        recordings.forEach(recording => {
            const date = new Date(recording.recorded_at);
            const formattedDate = `${date.getDate().toString().padStart(2, '0')}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getFullYear()}`;

            // ????? ???: ????? id ?? string ??? substring
            const shortId = recording.id ? String(recording.id).substring(0, 8) + '...' : 'N/A';

            const item = document.createElement('div');
            item.className = 'recording-item';
            item.innerHTML = `
            <div class="recording-item-header">
                <div class="recording-icon">
                    <i class="fa fa-play-circle"></i>
                </div>
                <div class="recording-details">
                    <div class="recording-title">${recording.file_name}</div>
                    <div class="recording-date">
                        <i class="fa fa-calendar"></i> ${formattedDate}
                    </div>
                    <div class="recording-id">ID: ${shortId}</div>
                </div>
            </div>
            <div class="recording-actions">
                <button class="btn-download" onclick="downloadRecording('${recording.file_path}', '${recording.file_name}')">
                    <i class="fa fa-download"></i> Download
                </button>
                <button class="btn-play" onclick="openRecording('${recording.file_path}')">
                    <i class="fa fa-play"></i> Play
                </button>
                <button class="btn-copy" onclick="copyRecordingLink('${recording.file_path}')">
                    <i class="fa fa-copy"></i>
                </button>
                <button class="btn-delete np-delete-btn" data-rec-id="${recording.id}" data-rec-name="${recording.file_name.replace(/'/g, '&#39;')}">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
            container.appendChild(item);
        });
    }
    function deleteRecording(id, name) {
        if (!confirm('Are you sure you want to delete this recording?\n\n' + name + '\n\nThis action cannot be undone.')) {
            return;
        }
        fetch('/recordings/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Reload recordings list
                    if (typeof loadRecordings === 'function') {
                        loadRecordings();
                    } else {
                        location.reload();
                    }
                } else {
                    alert('Failed to delete: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => alert('Error: ' + err.message));
    }

    function copyRecordingLink(filePath) {
        const url = window.location.origin + '/' + filePath;

        navigator.clipboard.writeText(url)
            .then(() => {
                alert('?? Link copied to clipboard!');
            })
            .catch(err => {
                console.error('Copy failed:', err);
            });
    }


    // Function to open recording in new tab
    function openRecording(filePath) {
        const url = '/' + filePath;
        window.open(url, '_blank');
    }

    // Function to download recording
    function downloadRecording(filePath, fileName) {
        const link = document.createElement('a');
        link.href = '/' + filePath;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Function to share recording (optional)
    function shareRecording(filePath, fileName) {
        const url = window.location.origin + '/' + filePath;
        const shareData = {
            title: fileName,
            text: 'Check out this recording',
            url: url
        };

        if (navigator.share && navigator.canShare(shareData)) {
            navigator.share(shareData);
        } else {
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            });
        }
    }

    // Function to start recording demo
    function startRecordingDemo() {
        window.location.href = '/meeting/{{ auth()->user()->username ?? "demo" }}';
    }

    // Search button functionality
    document.getElementById('searchBtn')?.addEventListener('click', function () {
        const searchInput = document.getElementById('searchRecordings');
        const searchTerm = searchInput.value.toLowerCase();

        if (window.allRecordings) {
            const filtered = window.allRecordings.filter(recording =>
                recording.file_name.toLowerCase().includes(searchTerm) ||
                recording.recorded_at.toLowerCase().includes(searchTerm)
            );
            renderRecordings(filtered);
        }
    });

    // Call loadRecordings when page loads
    document.addEventListener('DOMContentLoaded', function () {
        loadRecordings();
    });
</script>
@endsection