export default function AdvertisementApiList() {
    let baseUrl = "http://192.168.0.140:8000"

    let apiList = {
        api_getTradeLicenseByHolding: `${baseUrl}/api/advertisement/self-advert/get-license-by-holding-no`, //list of ulb
        api_getTradeLicenseByUserId: `${baseUrl}/api/advertisement/self-advert/get-license-by-userid`, //list of ulb
        api_getTradeLicenseDetails: `${baseUrl}/api/advertisement/self-advert/get-details-by-license-no`, //list of ulb
        api_getUlbList: `${baseUrl}/api/get-all-ulb`, //list of ulb
        api_getWardList: `${baseUrl}/api/workflow/getWardByUlb`, //list of ulb
        api_getAdvertMasterData: `${baseUrl}/api/crud/param-strings`, //master data for self advertisement
        api_getSelfAdvertDocList: `${baseUrl}/api/advertisements/crud/v1/document-mstrs`, //applying for self advertisement
        api_postSelfAdvertApplication: `${baseUrl}/api/advertisement/self-advert/save`, //applying for self advertisement
        api_postMovableVehicleApplication: `${baseUrl}/api/advertisement/movable-vehicle/save`, //applying for self advertisement
        api_postPrivateLandApplication: `${baseUrl}/api/advertisement/private-land/save`, //applying for self advertisement
        api_postAgencyApplication: `${baseUrl}/api/advertisement/agency/save`, //applying for self advertisement
        api_getAppliedApplicationList: `${baseUrl}/api/advertisement/self-advert/get-citizen-applications`, //applying for self advertisement
        api_getAppliedApplicationDetail: `${baseUrl}/api/advertisement/self-advert/details`, //applying for self advertisement
        api_getAppliedDocumentList: `${baseUrl}/api/advertisement/self-advert/advertisement-document-view`, //applying for self advertisement
        api_getApprovedApplicationList: `${baseUrl}/api/advertisement/self-advert/approved-list`, //applying for self advertisement
        api_getRejectedApplicationList: `${baseUrl}/api/advertisement/self-advert/rejected-list`, //applying for self advertisement


        api_getMovableAppliedApplicationList: `${baseUrl}/api/advertisement/movable-vehicle/get-citizen-applications`, //applying for self advertisement
        api_getMovableApprovedApplicationList: `${baseUrl}/api/advertisement/movable-vehicle/approved-list`, //applying for self advertisement
        api_getMovableRejectedApplicationList: `${baseUrl}/api/advertisement/movable-vehicle/rejected-list`, //applying for self advertisement


        api_getPrivateLandAppliedApplicationList: `${baseUrl}/api/advertisement/private-land/get-citizen-applications`, //applying for self advertisement
        api_getPrivateLandApprovedApplicationList: `${baseUrl}/api/advertisement/private-land/approved-list`, //applying for self advertisement
        api_getPrivateLandRejectedApplicationList: `${baseUrl}/api/advertisement/private-land/rejected-list`, //applying for self advertisement


        api_getAgencyTypologyList: `${baseUrl}/api/advertisement/agency/get-typology-list`, //applying for self advertisement
        api_getAgencyApprovedApplicationList: `${baseUrl}/api/advertisement/agency/approved-list`, //applying for self advertisement
        api_getAgencyRejectedApplicationList: `${baseUrl}/api/advertisement/agency/rejected-list`, //applying for self advertisement
        api_getAgencyAppliedApplicationList: `${baseUrl}/api/advertisement/agency/get-citizen-applications`, //applying for self advertisement
        api_getAgencyApplicationFullDetail: `${baseUrl}/api/advertisement/agency/details`, //applying for self advertisement
        api_getAgencyAppliedDocumentList: `${baseUrl}/api/advertisement/agency/agency-document-view`, //applying for self advertisement


        api_postHoardingApplication: `${baseUrl}/api/advertisement/agency/save-for-licence`, //applying for self advertisement
        api_getHoardingPendingApplicationList: `${baseUrl}/api/advertisement/agency/license-get-citizen-applications`, //applying for self advertisement
        api_getHoardingApprovedApplicationList: `${baseUrl}/api/advertisement/agency/license-approved-list`, //applying for self advertisement
        api_getHoardingRejectedApplicationList: `${baseUrl}/api/advertisement/agency/license-rejected-list`, //applying for self advertisement

    }

    return apiList
}