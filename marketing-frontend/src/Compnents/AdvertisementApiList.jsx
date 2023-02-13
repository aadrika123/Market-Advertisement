export default function AdvertisementApiList() {
    let baseUrl = "http://192.168.0.127:8000"

    let apiList = {
        api_getTradeLicenseByHolding: `${baseUrl}/api/advertisement/self-advert/get-license-by-holding-no`, //trade license for holding
        api_getTradeLicenseByUserId: `${baseUrl}/api/advertisement/self-advert/get-license-by-userid`, //trade license list by userId
        api_getTradeLicenseDetails: `${baseUrl}/api/advertisement/self-advert/get-details-by-license-no`, //list of ulb
        api_getUlbList: `${baseUrl}/api/get-all-ulb`, //list of ulb
        api_getWardList: `${baseUrl}/api/workflow/getWardByUlb`, //list of ward
        api_getAdvertMasterData: `${baseUrl}/api/crud/param-strings`, //master data for advertisement
        api_getSelfAdvertDocList: `${baseUrl}/api/advertisements/crud/v1/document-mstrs`, //master document for advertisement
        //razor pay api
        verifyPaymentStatus: `${baseUrl}/api/payment/verify-payment-status`, //POST // use to store the data if payment failed or success=> 

        // Self Advertisement api //
        api_postSelfAdvertApplication: `${baseUrl}/api/advert/self/add-new`, //applying for self advertisement
        api_getAppliedApplicationList: `${baseUrl}/api/advert/self/list-applied-applications`, //pending Application list
        api_getAppliedApplicationDetail: `${baseUrl}/api/advert/self/get-details-by-id `, //application details by id
        api_getAppliedDocumentList: `${baseUrl}/api/advert/self/view-advert-document`, //applied document list
        api_getApprovedApplicationList: `${baseUrl}/api/advert/self/list-approved`, //approved Application list
        api_getRejectedApplicationList: `${baseUrl}/api/advert/self/list-rejected`, //rejected Application list
        api_getOrderIdForPayment: `${baseUrl}/api/advert/self/generate-payment-order-id`,//order if for payment
        api_getApplicationDetailForPayment: `${baseUrl}/api/advert/self/get-application-details-for-payment`,//application details for payment

        // Movable api //
        api_postMovableVehicleApplication: `${baseUrl}/api/advert/vehicle/add-new`, //applying for Movable vehicle
        api_getMovableAppliedApplicationList: `${baseUrl}/api/advert/vehicle/list-applied-applications`, //pending Application list
        api_getMovableAppliedApplicationDetail: `${baseUrl}/api/advert/vehicle/get-details-by-id `, //application details by id
        api_getMovableAppliedDocumentList: `${baseUrl}/api/advert/vehicle/view-vehicle-documents`, //applied document list
        api_getMovableApprovedApplicationList: `${baseUrl}/api/advert/vehicle/list-approved`, //approved Application list
        api_getMovableRejectedApplicationList: `${baseUrl}/api/advert/vehicle/list-rejected`, //rejected Application list
        api_getMovableVehicleOrderIdForPayment: `${baseUrl}/api/advert/vehicle/generate-payment-order-id`,//order if for payment
        api_getMovableVehicleApplicationDetailForPayment: `${baseUrl}/api/advert/vehicle/get-application-details-for-payment`,//application details for payment


        // Private land api //
        api_postPrivateLandApplication: `${baseUrl}/api/advert/pvt-land/add-new	`, //applying for private land
        api_getPrivateLandAppliedApplicationList: `${baseUrl}/api/advert/pvt-land/list-applied-applications`, //pending Application list
        api_getPrivateLandAppliedApplicationDetail: `${baseUrl}/api/advert/pvt-land/get-details-by-id `, //application details by id
        api_getPrivateLandAppliedDocumentList: `${baseUrl}/api/advert/pvt-land/view-pvt-land-documents`, //applied document list
        api_getPrivateLandApprovedApplicationList: `${baseUrl}/api/advert/pvt-land/list-approved`, //approved Application list
        api_getPrivateLandRejectedApplicationList: `${baseUrl}/api/advert/pvt-land/list-rejected  `, //rejected Application list
        api_getPrivateLandOrderIdForPayment: `${baseUrl}/api/advert/pvt-land/generate-payment-order-id`,//order if for payment
        api_getPrivateLandApplicationDetailForPayment: `${baseUrl}/api/advert/pvt-land/get-application-details-for-payment`,//application details for payment

        // Agency api //
        api_postAgencyApplication: `${baseUrl}/api/advertisement/agency/save`, //applying for agency
        api_getAgencyApprovedApplicationList: `${baseUrl}/api/advertisement/agency/approved-list`, //approved Application list
        api_getAgencyRejectedApplicationList: `${baseUrl}/api/advertisement/agency/rejected-list`, //rejected Application list
        api_getAgencyAppliedApplicationList: `${baseUrl}/api/advertisement/agency/get-citizen-applications`, //pending Application list
        api_getAgencyApplicationFullDetail: `${baseUrl}/api/advertisement/agency/details`, //application details by id
        api_getAgencyAppliedDocumentList: `${baseUrl}/api/advertisement/agency/agency-document-view`,//applied document list
        api_getAgencyOrderIdForPayment: `${baseUrl}/api/advertisement/agency/generate-payment-order-id`,//order if for payment
        api_getAgencyApplicationDetailForPayment: `${baseUrl}/api/advertisement/agency/application-details-for-payment`,//application details for payment
        api_postRedirectToAgencyDashboard: `${baseUrl}/api/advertisement/agency/is-agency`,//redirect to agency dashboard
        api_getAgencyDashboardData: `${baseUrl}/api/advertisement/agency/agency-dashboard`,//data in agency dashboard 
        // Hoarding api //
        api_postHoardingApplication: `${baseUrl}/api/advertisement/hording/licence-save`, //applying for hoarding
        api_getHoardingPendingApplicationList: `${baseUrl}/api/advertisement/hording/license-get-citizen-applications`, //pending Application list
        api_getHoardingApprovedApplicationList: `${baseUrl}/api/advertisement/hording/license-approved-list`, //approved Application list
        api_getHoardingRejectedApplicationList: `${baseUrl}/api/advertisement/hording/license-rejected-list`, //rejected Application list
        api_getHoardingTypologyList: `${baseUrl}/api/advertisement/hording/get-typology-list`, //topology list
        api_getHoardingApplicationFullDetail: `${baseUrl}/api/advertisement/hording/license-details`, //application details by id
        api_getHoardingAppliedDocumentList: `${baseUrl}/api/advertisement/hording/license-hording-document-view`,//applied document list
        api_getHoardingOrderIdForPayment: `${baseUrl}/api/advertisement/hording/license-generate-payment-order-id`,//order if for payment
        api_getHoardingApplicationDetailForPayment: `${baseUrl}/api/advertisement/hording/license-application-details-for-payment`,//application details for payment


        // marketing //

        // Banquet/marriage hall //
        api_postBanquetApplication: `${baseUrl}/api/market/banquet-marriage-hall/save`, //applying for hoarding
    }

    return apiList
}