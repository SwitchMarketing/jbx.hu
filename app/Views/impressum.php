<main>
    <section class="gap impressum">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h2>Impresszum</h2>
                    <h4>ADATKEZELŐ ADATAI</h4>
                    <table class="table">                        
                        <tbody>
                            <tr>
                                <td>Név</td>
                                <td><?php echo config( 'Config\\AppConfig' )->companyFullName ?></td>
                            </tr>                            
                            <tr>
                                <td>Székhely</td>
                                <td><?php echo config( 'Config\\AppConfig' )->companyAddress ?></td>
                            </tr>
                            <tr>
                                <td>Email cím</td>
                                <td><?php echo config( 'Config\\AppConfig' )->siteEmail ?></td>
                            </tr>
                            <tr>
                                <td>Cégjegyzékszám</td>
                                <td><?php echo config( 'Config\\AppConfig' )->companyRegNo ?></td>
                            </tr>
                            <tr>
                                <td>Adószám</td>
                                <td><?php echo config( 'Config\\AppConfig' )->companyTaxId ?></td>
                            </tr>
                            <tr>
                                <td>Nyilvántartásba vételt elrendelő hatóság</td>
                                <td>Budapest Környéki Törvényszék Cégbírósága</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>