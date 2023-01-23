import React, { useState } from 'react'
import { ColorRing, MutatingDots, Dna, CirclesWithBar, Oval } from 'react-loader-spinner';

function Loader(props) {
    // console.log('props loader ', props.show)
    return (
        <>
            {/* // <div className={`${props.show ? 'grid' : 'hidden'} bg-transparent bg z-20 absolute w-full h-screen`}>
        //     <div className="h-24 w-24 mx-auto my-[15%]"> */}

            <Oval
                visible={props.show}
                height="60"
                width="90"
                color="#666"
                wrapperStyle={{}}
                wrapperClass=""
                // visible={true}
                ariaLabel='oval-loading'
                secondaryColor="#666"
                strokeWidth="2"
                strokeWidthSecondary="1"

            />
            {/* <ColorRing
                visible={props.show}
                height="80"
                width="80"
                ariaLabel="blocks-loading"
                wrapperStyle={{}}
                wrapperClass="blocks-wrapper"
                colors={['#e15b64', '#f47e60', '#f8b26a', '#abbd81', '#849b87']}
            /> */}
            {/* <MutatingDots
                    height="100"
                    width="100"
                    color="#4fa94d"
                    secondaryColor='yellow'
                    radius='12.5'
                    ariaLabel="mutating-dots-loading"
                    wrapperStyle={{}}
                    wrapperClass=""
                    visible={true}
                /> */}
            {/* <Dna
                    visible={true}
                    height="80"
                    width="80"
                    ariaLabel="dna-loading"
                    wrapperStyle={{}}
                    wrapperClass="dna-wrapper"
                /> */}
            {/* <CirclesWithBar
                    height="100"
                    width="100"
                    color="mediumturquoise  "
                    wrapperStyle={{}}
                    wrapperClass=""
                    visible={true}
                    outerCircleColor="cornflowerblue "
                    innerCircleColor="darkturquoise "
                    barColor=""
                    ariaLabel='circles-with-bar-loading'
                /> */}
            {/*      </div>

         </div> */}
        </>
    )
}

export default Loader