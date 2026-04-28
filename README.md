# 🚀 M GRID Platform

M GRID is a digital empowerment platform designed to provide identity, credibility, and economic access for women, particularly in underserved and informal sectors.  
The system aggregates user data, verifies credentials, and translates real-world progress into a structured digital profile that can unlock opportunities such as funding, partnerships, and services.

## 🎯 Core Objective

To build a centralized, scalable ecosystem where women can:

- Establish a verified digital identity (**M-ID**)
- Track and improve their credibility score (**M SCORE**)
- Access financial services, training, and partnerships
- Participate in a structured economic network

## 🧩 Key Modules

### 1. M-ID (Digital Identity)

- Unique identifier for every registered user
- Serves as the foundation for all platform interactions
- Linked to uploaded documents and verification status

### 2. M-Profile (User Dashboard)

Central dashboard displaying:

- Personal information
- Uploaded documents
- Verification status
- Progress and activity

### 3. Document Management & Verification

Upload support for:

- National ID
- Business registration (BRELA)
- Tax documents (TRA)
- Bank details
- Certificates

Additional capabilities:

- Admin-based manual verification system
- Designed for future integration with APIs (e.g., NIDA)

### 4. M-Score (Credibility Engine)

Dynamic scoring system based on:

- Profile completion
- Verified documents
- Financial behavior
- Participation in programs

Used to determine eligibility for opportunities.

### 5. M-Fund (Financial Access)

Enables users to:

- Apply for funding/loans
- Be evaluated based on M-Score
- Integrate in future with financial institutions

### 6. Admin Panel

- User management
- Document verification
- System monitoring
- Role-based access (Admin vs User)

## 🌍 Localization Strategy

- **Primary language:** Kiswahili (simple, accessible)
- **Secondary language:** English
- Built for inclusivity, especially for low-income and non-English-speaking users

## 🛠 Tech Stack

- **Frontend:** HTML, CSS, Bootstrap (customized template)
- **Backend:** Plain PHP (no framework)
- **Database:** MySQL
- **Hosting:** Hostinger (Shared Hosting)
- **Version Control:** Git + GitHub
- **Deployment:** SSH-based Git pull workflow

## ⚙️ Deployment Workflow

The platform is deployed on Hostinger using SSH and Git:

```bash
cd ~/domains/mgrid.co.tz/public_html
git pull origin main
```

This allows fast updates without manual file uploads.

## 🔐 Authentication & Roles

- User registration and login
- Role separation:
  - Users (women participants)
  - Admins (verification and system control)

## 🔄 Future Enhancements

- NIDA API integration for automatic identity verification
- Partner integrations (Banks, BRELA, TRA, training institutions)
- Mobile-first progressive web app (PWA)
- Advanced analytics for M-Score
- Multi-tenant partner dashboards

## 💡 Vision

M GRID aims to become a Pan-African digital infrastructure for women's economic empowerment, enabling structured participation in the formal economy through technology, data, and partnerships.
