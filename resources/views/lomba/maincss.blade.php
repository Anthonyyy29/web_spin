<style>
    ::placeholder {
        color: #ddd !important;
        opacity: 1;
    }

    select.form-select option {
        background-color: rgb(28, 28, 29);
        /* Bootstrap .bg-dark */
        color: #ffffff;
        /* Bootstrap .text-white */
    }

    .bg-dark {
        background-color: rgba(22, 22, 22, 0.8) !important;
    }

    .btn-dark {
        background-color: rgb(22, 22, 22, 0.8) !important;
    }

    body {
        min-height: 100vh;
        background-image: url('{{ url('assets/img/lomba/bg-lomba.png') }}');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        background-attachment: fixed;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        /* backdrop-filter: blur(3px); */
        z-index: -1;
    }

    @media (max-width: 768px) {
        /* body {
            background-image: url('{{ url('assets/img/lomba/bg_mobile.png') }}');
        } */
            
    }

    @media (max-width: 500px) {
        h1 {
            font-size: 1.5rem;
        }
    }
</style>