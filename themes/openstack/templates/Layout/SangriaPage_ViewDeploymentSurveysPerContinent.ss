<% include SangriaPage_SurveyRangeSelector Label='Surveys Subset', FormAction=$Top.GetLinkForDeploymentSurveysPerContinent($continent), FromPage=ViewDeploymentSurveysPerRegion %>
<script type="application/javascript">
        $LoadJsonCountriesCoordinates('ViewDeploymentSurveysPerRegion')
    var countries_with_deployment = [];
</script>
<link rel="stylesheet" href="/themes/openstack/css/bootstrap.min.css" type="text/css" media="screen, projection">
<a href="{$Link(ViewDeploymentSurveysPerRegion)}">Back</a>
<h2> Deployment Surveys in $continent_name </h2>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-4">
            <% loop CountriesWithDeploymentSurveys($continent) %>

                <h3><a href="#" class="country_link" data-country="{$country}">$country_name ($count)</a></h3>
                <ul style="list-style: none">
                    <% loop $Top.DeploymentSurveysPerCountry($country) %>
                        <li>

                            <script type="application/javascript">
                                if(!countries_with_deployment.hasOwnProperty("{$PrimaryCountry}") )
                                    countries_with_deployment["{$PrimaryCountry}"] = new Array();
                                var deployments = countries_with_deployment["{$PrimaryCountry}"];
                                deployments.push({code:"{$PrimaryCountry}" , name : "{$Email} - {$Industry}", url: "{$Top.Link(SurveyDetails)}/{$ID}?BackUrl={$Top.Link(ViewDeploymentSurveysPerRegion)}%3Fcontinent%3D{$Top.continent}" });
                            </script>
                            <a href="{$Top.Link(SurveyDetails)}/{$ID}?BackUrl={$Top.Link(ViewDeploymentSurveysPerRegion)}%3Fcontinent%3D{$Top.continent}">$Org.Name</a>

                        </li>
                    <% end_loop %>
                </ul>

            <% end_loop %>
        </div>
        <div class="col-lg-8">
            <div style="width:100%; height: 650px; position: relative;" id="map" tabindex="0">
            </div>
        </div>
    </div>
</div>