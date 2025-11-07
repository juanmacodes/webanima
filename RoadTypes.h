#pragma once

#include "CoreMinimal.h"

class UMaterialInterface;
class UStaticMesh;
class AActor;

UENUM(BlueprintType)
enum class ERoadType : uint8
{
    Normal UMETA(DisplayName = "Normal"),
    Bridge UMETA(DisplayName = "Bridge"),
    Tunnel UMETA(DisplayName = "Tunnel"),
    Ramp UMETA(DisplayName = "Ramp")
};

UENUM(BlueprintType)
enum class EIntersectionType : uint8
{
    Crossroad UMETA(DisplayName = "Crossroad"),
    ThreeWay UMETA(DisplayName = "Three Way"),
    YIntersection UMETA(DisplayName = "Y Intersection"),
    Roundabout UMETA(DisplayName = "Roundabout")
};

USTRUCT(BlueprintType)
struct FRoadProfile
{
    GENERATED_BODY()

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    int32 LanesPerDirection = 1;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    float LaneWidth = 350.f;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    bool bHasSidewalks = false;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    float SidewalkWidth = 150.f;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    float SidewalkHeight = 15.f;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    float CurbHeight = 10.f;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    UMaterialInterface* RoadMaterial = nullptr;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    UMaterialInterface* SidewalkMaterial = nullptr;
};

USTRUCT(BlueprintType)
struct FRoadConnection
{
    GENERATED_BODY()

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    AActor* ConnectedActor = nullptr;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    FVector Location = FVector::ZeroVector;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    FRotator Rotation = FRotator::ZeroRotator;
};

USTRUCT(BlueprintType)
struct FRoadPropSocket
{
    GENERATED_BODY()

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    UStaticMesh* PropMesh = nullptr;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    float IntervalDistance = 1000.f;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    FVector Offset = FVector::ZeroVector;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    FRotator RotationOffset = FRotator::ZeroRotator;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category = "Road")
    bool bAlignToRoad = true;
};
