export default function AdvertisementApiList() {
    let baseUrl = "http://192.168.0.214:8001"
    
    let apiList = {
        api_getUlbList: `${baseUrl}/api/get-all-ulb`, //list of ulb
        api_getAdvertMasterData: `${baseUrl}/api/crud/param-strings`, //master data for self advertisement
        api_getSelfAdvertDocList: `${baseUrl}/api/advertisements/crud/v1/document-mstrs`, //applying for self advertisement
        api_postSelfAdvertApplication: `${baseUrl}/api/advertisement/self-advert/save`, //applying for self advertisement
        api_postMovableVehicleApplication: `${baseUrl}/api/advertisement/movable-vehicle/save`, //applying for self advertisement
        api_postPrivateLandApplication: `${baseUrl}/api/advertisement/private-land/save`, //applying for self advertisement
        api_postAgencyApplication: `${baseUrl}/api/advertisements/agency/save`, //applying for self advertisement
        api_getAppliedApplicationList: `${baseUrl}/api/advertisement/self-advert/get-citizen-applications`, //applying for self advertisement
        api_getAppliedApplicationDetail: `${baseUrl}/api/advertisement/self-advert/details`, //applying for self advertisement
        

    }

    return apiList
}